# core/utils/perf.py
from collections import defaultdict
from dataclasses import dataclass
from datetime import timedelta
from statistics import median, pstdev

@dataclass
class PerfSummary:
    n: int
    wins: int
    losses: int
    flats: int
    net: float
    gross_profit: float
    gross_loss: float
    pf: float | None              # 数値（∞は float("inf")）
    pf_display: str               # テンプレ表示用
    winrate: float | None         # 0.0-1.0
    winrate_pct_display: str      # "60.0%"
    expectancy: float | None
    expectancy_display: str
    avg_win: float | None
    avg_win_display: str
    avg_loss: float | None        # 負値
    avg_loss_display: str
    payoff: float | None
    payoff_display: str
    median_pnl: float | None
    median_pnl_display: str
    std_pnl: float | None
    std_pnl_display: str
    trades_per_day: float | None
    trades_per_day_display: str
    avg_duration_display: str | None
    kelly_f: float | None         # -1.0〜1.0 程度
    kelly_pct_display: str
    by_symbol: list[dict]
    by_side: list[dict]
    by_hour: list[dict]
    streak_win_max: int
    streak_loss_max: int

def _fmt_num(x, digits=2, default="-"):
    if x is None:
        return default
    try:
        return f"{float(x):,.{digits}f}"
    except Exception:
        return default

def _fmt_pct(x, digits=1, default="-"):
    if x is None:
        return default
    try:
        return f"{float(x)*100:.{digits}f}%"
    except Exception:
        return default

def summarize(trades) -> PerfSummary:
    pnls = []
    durations = []
    wins = losses = flats = 0
    gross_profit = 0.0
    gross_loss = 0.0
    by_symbol = defaultdict(list)
    by_side   = defaultdict(list)
    by_hour   = defaultdict(list)

    # 期間算出用
    min_ts = None
    max_ts = None

    # 連勝/連敗用
    streak = 0
    streak_win_max = 0
    streak_loss_max = 0

    for t in trades:
        pnl = float(getattr(t, "net_profit", 0) or 0)
        pnls.append(pnl)

        if pnl > 0:
            wins += 1
            gross_profit += pnl
            streak = streak + 1 if streak >= 0 else 1
            streak_win_max = max(streak_win_max, streak)
        elif pnl < 0:
            losses += 1
            gross_loss += abs(pnl)
            streak = streak - 1 if streak <= 0 else -1
            streak_loss_max = max(streak_loss_max, abs(streak))
        else:
            flats += 1
            streak = 0

        if getattr(t, "open_time", None) and getattr(t, "close_time", None):
            durations.append(t.close_time - t.open_time)

        by_symbol[getattr(t, "symbol", "")].append(pnl)
        by_side[getattr(t, "side", "")].append(pnl)
        if getattr(t, "open_time", None):
            by_hour[t.open_time.hour].append(pnl)

        if getattr(t, "open_time", None):
            min_ts = t.open_time if min_ts is None else min(min_ts, t.open_time)
        if getattr(t, "close_time", None):
            max_ts = t.close_time if max_ts is None else max(max_ts, t.close_time)

    n = len(pnls)
    net = sum(pnls)

    avg_win     = (sum(p for p in pnls if p > 0) / wins) if wins else None
    avg_loss    = (sum(p for p in pnls if p < 0) / losses) if losses else None  # 負値
    winrate     = (wins / n) if n else None
    payoff      = (avg_win / abs(avg_loss)) if (avg_win and avg_loss) else None
    expectancy  = (net / n) if n else None
    std_pnl     = pstdev(pnls) if n >= 2 else None
    median_pnl  = median(pnls) if n else None
    avg_duration= (sum(durations, timedelta()) / len(durations)) if durations else None

    # PF（GL=0 & GP>0 -> ∞、GL=0 & GP=0 -> 0）
    pf = (gross_profit / gross_loss) if gross_loss > 0 else (float("inf") if gross_profit > 0 else 0.0)
    pf_display = "∞" if pf == float("inf") else _fmt_num(pf, 2)

    # 期間あたり密度（Trades/Day）— 先に計算してから display を作る
    trades_per_day = None
    if min_ts and max_ts and max_ts > min_ts:
        days = (max_ts - min_ts).total_seconds() / 86400
        if days > 0:
            trades_per_day = n / days

    # ケリー（参考値）
    kelly_f = None
    if winrate is not None and payoff:
        p = winrate
        b = payoff
        kelly_f = p - (1 - p) / b

    # 表示用文字列（テンプレでは計算しない）
    winrate_pct_display    = _fmt_pct(winrate, 1)
    expectancy_display     = _fmt_num(expectancy, 2)
    avg_win_display        = _fmt_num(avg_win, 2)
    avg_loss_display       = _fmt_num(avg_loss, 2)
    payoff_display         = _fmt_num(payoff, 2)
    median_pnl_display     = _fmt_num(median_pnl, 2)
    std_pnl_display        = _fmt_num(std_pnl, 2)
    trades_per_day_display = _fmt_num(trades_per_day, 2)
    avg_duration_display   = str(avg_duration) if avg_duration is not None else "-"
    kelly_pct_display      = _fmt_pct(kelly_f, 1)

    def pack_breakdown(dct):
        out = []
        for key, arr in sorted(dct.items(), key=lambda kv: str(kv[0])):
            if not arr:
                continue
            gp = sum(p for p in arr if p > 0.0)
            gl = abs(sum(p for p in arr if p < 0.0))
            pf_local = (gp / gl) if gl > 0 else (float("inf") if gp > 0 else 0.0)
            out.append({
                "key": key,
                "n": len(arr),
                "winrate_pct_display": _fmt_pct(sum(1 for p in arr if p > 0)/len(arr), 1),
                "pf_display": "∞" if pf_local == float("inf") else _fmt_num(pf_local, 2),
                "expect_display": _fmt_num(sum(arr)/len(arr), 2),
                "median_display": _fmt_num(median(arr), 2),
            })
        return out

    return PerfSummary(
        n=n, wins=wins, losses=losses, flats=flats,
        net=net,
        gross_profit=gross_profit,
        gross_loss=gross_loss,
        pf=pf,
        pf_display=pf_display,
        winrate=winrate,
        winrate_pct_display=winrate_pct_display,
        expectancy=expectancy,
        expectancy_display=expectancy_display,
        avg_win=avg_win,
        avg_win_display=avg_win_display,
        avg_loss=avg_loss,
        avg_loss_display=avg_loss_display,
        payoff=payoff,
        payoff_display=payoff_display,
        median_pnl=median_pnl,
        median_pnl_display=median_pnl_display,
        std_pnl=std_pnl,
        std_pnl_display=std_pnl_display,
        trades_per_day=trades_per_day,
        trades_per_day_display=trades_per_day_display,
        avg_duration_display=avg_duration_display,
        kelly_f=kelly_f,
        kelly_pct_display=kelly_pct_display,
        by_symbol=pack_breakdown(by_symbol),
        by_side=pack_breakdown(by_side),
        by_hour=pack_breakdown(by_hour),
        streak_win_max=streak_win_max,
        streak_loss_max=streak_loss_max,
    )
