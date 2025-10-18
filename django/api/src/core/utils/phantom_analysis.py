"""
phantom_analysis.py
-------------------
PhantomJob（magic）に紐づく ClosedPosition から
- path_analysis（例: ["Q1","Mid","Q3","TP"] のような経路）
- r_analysis   （レッグ単位でR集計し、スロット集計/合計Rも付与）
- total_pnl    （ClosedPosition.profit の合計 / 口座通貨）
を導出し、必要に応じて PhantomJob に保存します。

方針（パス分析）:
- オープン時は起点スロット（Q1/Mid/Q3）をイベント化
- クローズ時は Outcome(TP/SL) をまず確定（コメント優先、なければ損益）
  * TPのとき:
      - 分割TP(Q1k1, Q1k2, Midk1) は遷移先スロット（Mid/Q3）をイベント化
      - 最終TP(Q1k3, Midk2, Q3k1) は "TP" をイベント化（終端）
  * SLのとき: "SL" をイベント化（終端）
- 連続重複は圧縮し、最初の終端（TP/SL）以降は切り捨て

使い方:
    # ドライラン（保存しない）
    python manage.py analyze_phantomjob <magic>

    # 保存する
    python manage.py analyze_phantomjob <magic> --apply

前提:
- core.models に PhantomJob, ClosedPosition が存在
- ClosedPosition は magic, open_time, close_time, profit, comment を持つ
- PhantomJob には path_analysis(JSONField), r_analysis(JSONField), total_pnl(DecimalField) がある
- R集計は「レッグ単位（Q1k1, Q1k2, Q1k3, Midk1, Midk2, Q3k1）」で行い、スロット（Q1/Mid/Q3）へ集約する
"""

from __future__ import annotations

import re
from collections import defaultdict
from typing import Dict, List, Tuple, Optional
from decimal import Decimal

from django.db import transaction
from django.utils import timezone
from django.core.exceptions import ObjectDoesNotExist

# ★ プロジェクトの実パスに合わせて調整してください
from core.models import PhantomJob, ClosedPosition


# =========================
# 正規表現（コメント解析）
# =========================
# 例:
#   "GRID Q3 k1[tp]"
#   "GRID MID k2[sl]"
#   "q1   k3 [ TP ]"
LEG_OUTCOME_RE = re.compile(
    r"(Q\s*([123])|MID)\s*K\s*([123])\s*\[\s*(TP|SL)\s*\]",
    re.IGNORECASE,
)
LEG_ONLY_RE = re.compile(
    r"(Q\s*([123])|MID)\s*K\s*([123])",
    re.IGNORECASE,
)


def _parse_comment_for_leg_outcome(comment: str) -> Tuple[Optional[str], Optional[str]]:
    """
    コメントから (leg, outcome) を抽出する。
    - leg: 'Q1k1' / 'Midk1' / 'Q3k1' 形式 or None
    - outcome: 'TP' / 'SL' or None
    'MID' は 'Midk*' に正規化する（Q2表記は使わない）。
    """
    if not comment:
        return None, None

    m = LEG_OUTCOME_RE.search(comment)
    if m:
        whole, qnum, kval, outcome = m.groups()
        if whole.upper().startswith("MID"):
            leg = f"Midk{kval}"
        else:
            leg = f"Q{qnum}k{kval}"
        return leg, outcome.upper()

    m2 = LEG_ONLY_RE.search(comment)
    if m2:
        whole, qnum, kval = m2.groups()
        if whole.upper().startswith("MID"):
            leg = f"Midk{kval}"
        else:
            leg = f"Q{qnum}k{kval}"
        return leg, None

    return None, None


# =========================
# レッグ→スロットの対応表
# =========================
# 起点（オープン時）に付与するスロット
OPEN_SLOT_MAP: Dict[str, str] = {
    "Q1k1": "Q1", "Q1k2": "Q1", "Q1k3": "Q1",
    "Midk1": "Mid", "Midk2": "Mid",
    "Q3k1": "Q3",
}

# ※ 旧 CLOSE_SLOT_MAP は廃止（Outcome考慮不足のため）


# Outcome別の「クローズ時イベント」マップ
# - TP時：分割TPは遷移先スロット、最終TPは "TP"
# - SL時：全レッグ共通で "SL"
CLOSE_EVENT_MAP: Dict[str, Dict[str, str]] = {
    "TP": {
        "Q1k1": "Mid",   # 分割利確 → Midへ遷移
        "Q1k2": "Q3",    # 分割利確 → Q3へ遷移
        "Q1k3": "TP",    # 最終TP → 終端
        "Midk1": "Q3",   # 分割利確 → Q3へ遷移
        "Midk2": "TP",   # 最終TP → 終端
        "Q3k1": "TP",    # 最終TP → 終端
    },
    "SL": {
        "Q1k1": "SL", "Q1k2": "SL", "Q1k3": "SL",
        "Midk1": "SL", "Midk2": "SL",
        "Q3k1": "SL",
    },
}

# スロット→属するレッグ（集計用）
SLOT_TO_LEGS_FALLBACK: Dict[str, List[str]] = {
    "Q1":  ["Q1k1", "Q1k2", "Q1k3"],
    "Mid": ["Midk1", "Midk2"],
    "Q3":  ["Q3k1"],
}

# 表示順
SLOT_ORDER = ["Q1", "Mid", "Q3"]  # スロット順：Q1 -> Mid -> Q3
LEG_ORDER  = ["Q1k1", "Q1k2", "Q1k3", "Midk1", "Midk2", "Q3k1"]  # レッグ順


def _order_by_leg(d: dict) -> dict:
    """LEG_ORDER に従ってキー順を揃える。未知キーは最後に現状順で付ける。"""
    ordered = {k: d[k] for k in LEG_ORDER if k in d}
    for k in d.keys():
        if k not in ordered:
            ordered[k] = d[k]
    return ordered


# =========================
# R倍率（レッグ単位）
# =========================
# Q1k1 -> Risk=-R,   Return=+R
# Q1k2 -> Risk=-R,   Return=+2R
# Q1k3 -> Risk=-R,   Return=+3R
# Midk1 -> Risk=-2R, Return=+R
# Midk2 -> Risk=-2R, Return=+2R
# Q3k1 -> Risk=-3R,  Return=+1R  （※ ret は 1R 設計想定／要件に応じ調整）
SLOT_RISK_RETURN_PER_LEG_FALLBACK: Dict[str, Dict[str, float]] = {
    "Q1k1": {"risk": 1.0, "ret": 1.0},
    "Q1k2": {"risk": 1.0, "ret": 2.0},
    "Q1k3": {"risk": 1.0, "ret": 3.0},
    "Midk1": {"risk": 2.0, "ret": 1.0},
    "Midk2": {"risk": 2.0, "ret": 2.0},
    "Q3k1": {"risk": 3.0, "ret": 1.0},
}


# =========================
# 小さなヘルパー
# =========================
def _run_length_dedupe(seq: List[str]) -> List[str]:
    """連続重複の圧縮（['Q1','Q1','Mid','Mid','TP'] -> ['Q1','Mid','TP']）"""
    out: List[str] = []
    prev = object()
    for x in seq:
        if x != prev:
            out.append(x)
            prev = x
    return out


def _compute_R_per_leg(per_leg_seq: Dict[str, List[str]],
                       leg_riskret: Dict[str, Dict[str, float]]) -> Tuple[Dict[str, float], float]:
    """
    レッグ単位の TP/SL 列（per_leg_seq）と、レッグごとの risk/ret（leg_riskret）から
    - by_leg_R: レッグごとの純R
    - total_R: すべての合計R
    を返す。戻り dict は LEG_ORDER 順でソートして返す。
    """
    by_leg_R_raw: Dict[str, float] = {}
    total_R = 0.0
    for leg, outcomes in per_leg_seq.items():
        cfg = leg_riskret.get(leg, {"risk": 1.0, "ret": 1.0})
        risk = float(cfg.get("risk", 1.0))
        ret = float(cfg.get("ret", 1.0))
        net = 0.0
        for oc in outcomes:
            if oc == "TP":
                net += ret
            elif oc == "SL":
                net -= risk
        net = round(net, 6)
        by_leg_R_raw[leg] = net
        total_R += net

    by_leg_R = _order_by_leg(by_leg_R_raw)  # ← レッグ順固定
    return by_leg_R, round(total_R, 6)


def _aggregate_R_to_slots(by_leg_R: Dict[str, float],
                          slot_to_legs: Dict[str, List[str]]) -> Dict[str, float]:
    """
    レッグごとの R を、スロット（Q1/Mid/Q3）へ集約する。
    返す dict は `Q1, Mid, Q3` の順序で保存される。
    """
    by_slot_R_ordered: Dict[str, float] = {}
    for slot in SLOT_ORDER:  # ← スロット順固定
        acc = 0.0
        for leg in slot_to_legs.get(slot, []):
            acc += by_leg_R.get(leg, 0.0)
        by_slot_R_ordered[slot] = round(acc, 6)
    return by_slot_R_ordered


# =========================
# エントリ関数
# =========================
def analyze_phantom_job(magic: int, *, dry_run: bool = True) -> Dict:
    """
    指定 magic の PhantomJob について:
      1) ClosedPosition を抽出
      2) path_analysis を構築（open/close をイベント化→時系列→RLE圧縮→終端で打ち切り）
      3) r_analysis   を構築（レッグ単位で TP/SL を積む→レッグR計算→スロット集計）
      4) total_pnl    を計算（ClosedPosition.profit の合計）
      5) dry_run=False の場合、PhantomJob に保存

    戻り値(dict):
      {
        'job_id': int,
        'path_analysis': List[str],
        'r_analysis': {
            'per_leg':   {...}  # Q1k1->Q1k2->Q1k3->Midk1->Midk2->Q3k1 の順
            'per_slot':  {...}  # Q1->Mid->Q3 の順
            'by_leg_R':  {...}  # 上記レッグ順
            'by_slot_R': {...}  # 上記スロット順
            'total_R':   float
        },
        'total_pnl': Decimal,
        'counts': {'closed_positions': int, 'events': int},
        'saved': bool
      }
    """
    try:
        job = PhantomJob.objects.get(magic=magic)
    except ObjectDoesNotExist:
        raise ValueError(f"PhantomJob with magic={magic} not found.")

    # 設定（モデル側が優先 / なければフォールバック）
    leg_riskret = (
        getattr(PhantomJob, "SLOT_RISK_RETURN_PER_LEG", None)
        or getattr(job, "SLOT_RISK_RETURN_PER_LEG", None)
        or SLOT_RISK_RETURN_PER_LEG_FALLBACK
    )
    slot_to_legs = (
        getattr(PhantomJob, "SLOT_TO_LEGS", None)
        or getattr(job, "SLOT_TO_LEGS", None)
        or SLOT_TO_LEGS_FALLBACK
    )

    # 同一 magic の決済履歴を抽出（必要列のみ）
    cps = (
        ClosedPosition.objects
        .filter(magic=magic)
        .only("open_time", "close_time", "profit", "comment")
    )

    total_cps = cps.count()

    # --- パス分析（open/close をイベント化）と R 分布（レッグ単位） ---
    events: List[Tuple] = []
    per_leg_seq: Dict[str, List[str]] = defaultdict(list)   # レッグ→[TP/SL,...]
    per_slot_seq: Dict[str, List[str]] = defaultdict(list)  # 参考（スロット→[TP/SL,...]）

    # total_pnl（Decimal）の集計
    total_pnl: Decimal = Decimal("0.00")

    for cp in cps:
        leg, outcome_from_cmt = _parse_comment_for_leg_outcome(cp.comment or "")
        if not leg:
            # レッグ不明は除外（必要なら warn ログに変更）
            continue

        open_slot = OPEN_SLOT_MAP.get(leg)

        # パス分析用：オープンは起点スロットをイベント化
        if cp.open_time and open_slot:
            events.append((cp.open_time, open_slot))

        # R 分布用：レッグ単位に結果を積む（コメント最優先 / 無ければ利益符号で補完）
        if cp.close_time is not None:
            if outcome_from_cmt in {"TP", "SL"}:
                outcome = outcome_from_cmt
            else:
                outcome = "TP" if (cp.profit or 0) > 0 else "SL"

            per_leg_seq[leg].append(outcome)
            if open_slot:
                per_slot_seq[open_slot].append(outcome)  # 参考用

            # クローズ側イベント（Outcomeで分岐して文字列化）
            close_token = CLOSE_EVENT_MAP.get(outcome, {}).get(leg)
            if not close_token:
                # 不測パターンがあれば Outcome をそのまま使う（フォールバック）
                close_token = outcome

            events.append((cp.close_time, close_token))

        # total_pnl を積み上げ（None ガード）
        if getattr(cp, "profit", None) is not None:
            total_pnl += Decimal(cp.profit)

    # パス整形：時系列→トークン列→連続重複圧縮
    events.sort(key=lambda x: x[0])
    path_tokens = [tok for _, tok in events]
    path_analysis = _run_length_dedupe(path_tokens)

    # 最初に現れた終端（TP/SL）以降は切り捨て（終端の混入防止）
    for i, tok in enumerate(path_analysis):
        if tok in {"TP", "SL"}:
            path_analysis = path_analysis[: i + 1]
            break

    # レッグ単位 R 集計（戻り時に by_leg_R はレッグ順固定）
    by_leg_R, total_R = _compute_R_per_leg(per_leg_seq, leg_riskret)

    # スロットへ集約（戻り時にスロット順固定）
    by_slot_R = _aggregate_R_to_slots(by_leg_R, slot_to_legs)

    # 表示順を固定（per_leg）
    per_leg_ordered = _order_by_leg(dict(per_leg_seq))

    # per_slot は挿入順で Q1, Mid, Q3 となるよう生成
    per_slot_ordered = {}
    for s in SLOT_ORDER:
        per_slot_ordered[s] = per_slot_seq.get(s, [])

    r_analysis = {
        "per_leg":   per_leg_ordered,   # Q1k1->Q1k2->Q1k3->Midk1->Midk2->Q3k1
        "per_slot":  per_slot_ordered,  # Q1->Mid->Q3
        "by_leg_R":  by_leg_R,          # 上記レッグ順
        "by_slot_R": by_slot_R,         # 上記スロット順
        "total_R":   total_R,
    }

    result = {
        "job_id": job.id,
        "path_analysis": path_analysis,
        "r_analysis": r_analysis,
        "total_pnl": total_pnl,
        "counts": {
            "closed_positions": total_cps,
            "events": len(events),
        },
        "saved": False,
    }

    # --- 出力（ドライラン or 保存） ---
    if dry_run:
        print(f"[DRY-RUN] PhantomJob#{job.id} (magic={magic})")
        print(f"  path_analysis: {path_analysis}")
        print(f"  r_analysis: {r_analysis}")
        print(f"  total_pnl: {total_pnl}")
        return result

    # 保存
    with transaction.atomic():
        job.path_analysis = path_analysis
        job.r_analysis = r_analysis
        job.total_pnl = total_pnl
        job.updated_at = timezone.now()
        job.save(update_fields=["path_analysis", "r_analysis", "total_pnl", "updated_at"])
        result["saved"] = True

    print(f"[APPLIED] PhantomJob#{job.id} (magic={magic}) saved. path_analysis={path_analysis}, r_analysis={r_analysis}, total_pnl={total_pnl}")
    return result


__all__ = [
    "analyze_phantom_job",
]
