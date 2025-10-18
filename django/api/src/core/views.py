import os, requests
from collections import Counter
import logging
from datetime import timezone as dt_tz
from django import forms
from django.core.exceptions import ValidationError
from django.views.generic import FormView
from django.contrib import messages
from django.contrib.admin.views.decorators import staff_member_required
from django.utils.dateparse import parse_date, parse_datetime
from django.shortcuts import redirect, get_object_or_404
from django.urls import reverse
from rest_framework import serializers, viewsets, mixins, status
from rest_framework.response import Response
from rest_framework.decorators import action
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from rest_framework_api_key.permissions import HasAPIKey
from rest_framework.filters import OrderingFilter, SearchFilter
from django_filters.rest_framework import DjangoFilterBackend

from django.db import transaction
from django.db.models import Case, When, IntegerField
from django.utils import timezone
from django.utils.decorators import method_decorator

from .models import (
    Account,
    AccountDailyStat,
    ClosedPosition,
    OpenOrder,
    OpenPosition,
    PhantomJob,
)
from .serializers import (
    PhantomJobSerializer,
    AccountDailyStatSerializer,
    ClosedPositionSerializer,
    OpenOrderSerializer,
    OpenPositionSerializer
)
from .utils.symbols import SYMBOL_CHOICES

logger = logging.getLogger(__name__)

def _resolve_active_account(acc_id: str):
    try:
        # is_active で絞るなら: Account.objects.get(account_id=acc_id, is_active=True)
        return Account.objects.get(account_id=acc_id)
    except Account.DoesNotExist:
        raise serializers.ValidationError({"account_id": f"Unknown account_id: {acc_id}"})

class HealthzView(APIView):
    authentication_classes = []
    permission_classes = []

    def get(self, request):
        return Response({"ok": True})

class AccountDailyStatViewSet(mixins.ListModelMixin,
                              mixins.CreateModelMixin,
                              viewsets.GenericViewSet):
    """
    GET /api/account-daily-stats/?account_id=MT5-xxxx&date_from=2025-10-01&date_to=2025-10-07
    POST /api/account-daily-stats/
      {
        "account_id": "MT5-xxxx",
        "date": "2025-10-07",
        "balance": "12345.67",
        "equity": "12500.12",
        "pnl": "-55.00"
      }
    """
    queryset = AccountDailyStat.objects.select_related("account").all()
    serializer_class = AccountDailyStatSerializer
    permission_classes = [IsAuthenticated | HasAPIKey]
    filter_backends = [DjangoFilterBackend, OrderingFilter, SearchFilter]
    filterset_fields = ["account__account_id", "date"]
    ordering_fields = ["date", "created_at"]
    search_fields = ["account__account_id"]

    def get_queryset(self):
        qs = super().get_queryset()
        account_id = self.request.query_params.get("account_id")
        date_from = self.request.query_params.get("date_from")
        date_to = self.request.query_params.get("date_to")

        if account_id:
            qs = qs.filter(account__account_id=account_id)

        if date_from:
            d0 = parse_date(date_from)
            if d0:
                qs = qs.filter(date__gte=d0)
        if date_to:
            d1 = parse_date(date_to)
            if d1:
                qs = qs.filter(date__lte=d1)

        return qs.order_by("-date", "-id")


class ClosedPositionViewSet(mixins.ListModelMixin,
                            mixins.CreateModelMixin,
                            viewsets.GenericViewSet):
    """
    GET /api/closed-positions/?account_id=MT5-xxxx&symbol=USDJPY#&from=2025-10-01T00:00:00Z&to=2025-10-07T23:59:59Z
    POST /api/closed-positions/ ・・・(Serializer参照)
    """
    queryset = ClosedPosition.objects.select_related("account").all()
    serializer_class = ClosedPositionSerializer
    permission_classes = [IsAuthenticated | HasAPIKey]
    filter_backends = [DjangoFilterBackend, OrderingFilter, SearchFilter]
    filterset_fields = ["account__account_id", "symbol", "side", "magic", "ticket"]
    ordering_fields = ["close_time", "profit", "commission", "swap", "volume", "created_at"]
    search_fields = ["account__account_id", "symbol", "comment"]

    def get_queryset(self):
        qs = super().get_queryset()
        p = self.request.query_params

        if acc := p.get("account_id"):
            qs = qs.filter(account__account_id=acc)
        if sym := p.get("symbol"):
            qs = qs.filter(symbol=sym)

        t_from = p.get("from")    # ISO8601 (TZ込み推奨)
        t_to   = p.get("to")
        if t_from:
            dt = parse_datetime(t_from)
            if dt:
                qs = qs.filter(close_time__gte=dt)
        if t_to:
            dt = parse_datetime(t_to)
            if dt:
                qs = qs.filter(close_time__lte=dt)

        # 差分カーソルの二次キーとして ticket を入れておくと並びが安定
        return qs.order_by("-close_time", "-ticket", "-id")


    @action(detail=False, methods=["get"], url_path="latest")
    def latest(self, request):
        """
        最新カーソル（close_time, ticket）を返す
        """
        acc = request.query_params.get("account_id")
        if not acc:
            return Response({"detail": "account_id is required"}, status=400)

        qs = self.get_queryset().filter(account__account_id=acc)
        sym = request.query_params.get("symbol")
        if sym:
            qs = qs.filter(symbol=sym)

        latest = qs.order_by("-close_time", "-ticket", "-id").first()
        if latest is None:
            return Response({
                "account_id": acc,
                "symbol": sym,
                "latest_close_time": "1900-01-01T00:00:00Z",
                "latest_ticket": 0,
                "count": 0,
            })

        return Response({
            "account_id": acc,
            "symbol": sym,
            "latest_close_time": latest.close_time.astimezone(dt_tz.utc).isoformat().replace("+00:00","Z"),
            "latest_ticket": latest.ticket or 0,
            "count": qs.count(),
        })


    

    @action(detail=False, methods=["post"], url_path="bulk")
    def bulk(self, request):
        rid = getattr(request, "_request_id", "-")
        if not isinstance(request.data, list):
            return Response({"detail": "list required", "request_id": rid}, status=400)

        created = updated = failed = 0
        errors = []
        err_counter = Counter()

        logger.info("rid=%s bulk.start count=%d", rid, len(request.data))

        with transaction.atomic():
            for idx, raw in enumerate(request.data):
                try:
                    s = self.get_serializer(data=raw)
                    if not s.is_valid():
                        failed += 1
                        keys = tuple(sorted(s.errors.keys())) or ("__nonfield__",)
                        err_counter[keys] += 1
                        errors.append({"index": idx, "error": s.errors})
                        continue

                    vd = dict(s.validated_data)
                    acc_id = vd.pop("account_id", None)
                    if not acc_id:
                        failed += 1
                        err_counter[("account_id",)] += 1
                        errors.append({"index": idx, "error": {"account_id": ["required"]}})
                        continue

                    try:
                        account = _resolve_active_account(acc_id)
                    except Exception as e:
                        failed += 1
                        err_counter[("account_id",)] += 1
                        errors.append({"index": idx, "error": {"account_id": [str(e)]}})
                        continue

                    ticket = vd.pop("ticket", None)
                    if ticket is None:
                        failed += 1
                        err_counter[("ticket",)] += 1
                        errors.append({"index": idx, "error": {"ticket": ["required"]}})
                        continue

                    obj, was_created = ClosedPosition.objects.update_or_create(
                        account=account, ticket=ticket, defaults=vd
                    )
                    created += 1 if was_created else 1  # ←「更新も1件」と数えたいなら else 1
                    updated += 0 if was_created else 1

                except Exception as e:
                    failed += 1
                    err_counter[("__exception__",)] += 1
                    errors.append({"index": idx, "error": {"__all__": [str(e)]}})
                    logger.exception("rid=%s bulk.unexpected idx=%d", rid, idx)

        ok = failed == 0
        logger.info("rid=%s bulk.done created=%d updated=%d failed=%d ok=%s error-summary=%s first-error=%s",
                    rid, created, updated, failed, ok, dict(err_counter), (errors[0] if errors else None))

        status_code = status.HTTP_201_CREATED if ok else status.HTTP_207_MULTI_STATUS
        error_summary = { "|".join(k) if isinstance(k, tuple) else str(k): v
                  for k, v in err_counter.items() }
        return Response(
            {"ok": ok, "created": created, "updated": updated, "failed": failed,
            "errors": errors[:10],  # ← レスポンスは先頭10件だけに制限（全部はログに残る）
            "error_summary": error_summary,
            "request_id": rid},
            status=status_code,
        )

class OpenOrderViewSet(mixins.ListModelMixin,
                       mixins.CreateModelMixin,
                       viewsets.GenericViewSet):
    """
    GET  /api/open-orders/?account_id=MT5-xxxx&symbol=USDJPY
    POST /api/open-orders/          ... 単体作成（Serializer準拠）
    POST /api/open-orders/bulk/     ... 複数 upsert（list）
    POST /api/open-orders/replace/  ... 口座単位 replace（全削除→bulk_create）
    """
    queryset = OpenOrder.objects.select_related("account").all()
    serializer_class = OpenOrderSerializer
    permission_classes = [IsAuthenticated | HasAPIKey]
    filter_backends = [DjangoFilterBackend, OrderingFilter, SearchFilter]
    filterset_fields = ["account__account_id", "symbol", "side", "magic", "ticket", "otype"]
    ordering_fields = ["snapshot_ts", "ticket", "volume", "placed_at"]
    search_fields = ["account__account_id", "symbol", "comment"]

    def get_queryset(self):
        qs = super().get_queryset()
        p = self.request.query_params
        if acc := p.get("account_id"):
            qs = qs.filter(account__account_id=acc)
        if sym := p.get("symbol"):
            qs = qs.filter(symbol=sym)
        return qs.order_by("-snapshot_ts", "-ticket", "-id")

    @action(detail=False, methods=["post"], url_path="bulk")
    def bulk(self, request):
        """
        list payload（各要素は OpenOrderSerializer に準拠）。
        口座×ticket で update_or_create（＝upsert）。
        """
        rid = getattr(request, "_request_id", "-")
        data = request.data
        if not isinstance(data, list):
            return Response({"detail": "list required", "request_id": rid}, status=400)

        created = updated = failed = 0
        errors = []
        err_counter = Counter()

        logger.info("rid=%s open-orders.bulk.start count=%d", rid, len(data))

        with transaction.atomic():
            for idx, raw in enumerate(data):
                try:
                    s = self.get_serializer(data=raw)
                    if not s.is_valid():
                        failed += 1
                        keys = tuple(sorted(s.errors.keys())) or ("__nonfield__",)
                        err_counter[keys] += 1
                        errors.append({"index": idx, "error": s.errors})
                        continue

                    vd = dict(s.validated_data)
                    acc_id = vd.pop("account_id", None)
                    ticket = vd.pop("ticket", None)
                    if not acc_id or ticket is None:
                        failed += 1
                        key = ("account_id", "ticket")
                        err_counter[key] += 1
                        errors.append({"index": idx, "error": {"account_id/ticket": ["required"]}})
                        continue

                    account = _resolve_active_account(acc_id)
                    obj, was_created = OpenOrder.objects.update_or_create(
                        account=account, ticket=ticket, defaults=vd
                    )
                    created += 1 if was_created else 1
                    updated += 0 if was_created else 1

                except Exception as e:
                    failed += 1
                    err_counter[("__exception__",)] += 1
                    errors.append({"index": idx, "error": {"__all__": [str(e)]}})
                    logger.exception("rid=%s open-orders.bulk.unexpected idx=%d", rid, idx)

        ok = failed == 0
        status_code = status.HTTP_201_CREATED if ok else status.HTTP_207_MULTI_STATUS
        error_summary = {"|".join(k) if isinstance(k, tuple) else str(k): v
                         for k, v in err_counter.items()}
        logger.info("rid=%s open-orders.bulk.done created=%d updated=%d failed=%d ok=%s",
                    rid, created, updated, failed, ok)
        return Response(
            {"ok": ok, "created": created, "updated": updated, "failed": failed,
             "errors": errors[:10], "error_summary": error_summary, "request_id": rid},
            status=status_code,
        )

    @action(detail=False, methods=["post"], url_path="replace")
    def replace(self, request):
        """
        口座単位で現在の open-orders を「全削除→一括作成」。
        payload 例:
        {
          "account_id": "MT5-xxxx",
          "snapshot_ts": "2025-10-14T01:23:45Z",
          "items": [ ... OpenOrderSerializer のlist ... ]
        }
        """
        rid = getattr(request, "_request_id", "-")
        payload = request.data
        if not isinstance(payload, dict):
            return Response({"detail": "object required", "request_id": rid}, status=400)

        acc_id = payload.get("account_id")
        snapshot_ts = payload.get("snapshot_ts")
        items = payload.get("items", [])

        if not acc_id or not snapshot_ts or not isinstance(items, list):
            return Response({"detail": "account_id, snapshot_ts, items are required",
                             "request_id": rid}, status=400)

        try:
            account = _resolve_active_account(acc_id)
        except Exception as e:
            return Response({"detail": str(e), "request_id": rid}, status=400)

        objs = []
        for idx, raw in enumerate(items):
            s = self.get_serializer(data={**raw, "account_id": acc_id, "snapshot_ts": snapshot_ts})
            if not s.is_valid():
                return Response({"detail": "invalid item", "index": idx, "errors": s.errors,
                                 "request_id": rid}, status=400)
            vd = dict(s.validated_data)
            vd.pop("account_id", None)
            objs.append(OpenOrder(account=account, **vd))

        with transaction.atomic():
            OpenOrder.objects.filter(account=account).delete()
            OpenOrder.objects.bulk_create(objs)

        return Response({"ok": True, "count": len(objs), "request_id": rid}, status=201)

class OpenPositionViewSet(mixins.ListModelMixin,
                          mixins.CreateModelMixin,
                          viewsets.GenericViewSet):
    """
    GET  /api/open-positions/?account_id=MT5-xxxx&symbol=USDJPY
    POST /api/open-positions/          ... 単体作成
    POST /api/open-positions/bulk/     ... 複数 upsert（list）
    POST /api/open-positions/replace/  ... 口座単位 replace（全削除→bulk_create）
    """
    queryset = OpenPosition.objects.select_related("account").all()
    serializer_class = OpenPositionSerializer
    permission_classes = [IsAuthenticated | HasAPIKey]
    filter_backends = [DjangoFilterBackend, OrderingFilter, SearchFilter]
    filterset_fields = ["account__account_id", "symbol", "side", "magic", "ticket"]
    ordering_fields = ["snapshot_ts", "ticket", "volume", "open_time"]
    search_fields = ["account__account_id", "symbol", "comment"]

    def get_queryset(self):
        qs = super().get_queryset()
        p = self.request.query_params
        if acc := p.get("account_id"):
            qs = qs.filter(account__account_id=acc)
        if sym := p.get("symbol"):
            qs = qs.filter(symbol=sym)
        return qs.order_by("-snapshot_ts", "-ticket", "-id")

    @action(detail=False, methods=["post"], url_path="bulk")
    def bulk(self, request):
        rid = getattr(request, "_request_id", "-")
        data = request.data
        if not isinstance(data, list):
            return Response({"detail": "list required", "request_id": rid}, status=400)

        created = updated = failed = 0
        errors = []
        err_counter = Counter()

        logger.info("rid=%s open-positions.bulk.start count=%d", rid, len(data))

        with transaction.atomic():
            for idx, raw in enumerate(data):
                try:
                    s = self.get_serializer(data=raw)
                    if not s.is_valid():
                        failed += 1
                        keys = tuple(sorted(s.errors.keys())) or ("__nonfield__",)
                        err_counter[keys] += 1
                        errors.append({"index": idx, "error": s.errors})
                        continue

                    vd = dict(s.validated_data)
                    acc_id = vd.pop("account_id", None)
                    ticket = vd.pop("ticket", None)
                    if not acc_id or ticket is None:
                        failed += 1
                        err_counter[("account_id/ticket",)] += 1
                        errors.append({"index": idx, "error": {"account_id/ticket": ["required"]}})
                        continue

                    account = _resolve_active_account(acc_id)
                    obj, was_created = OpenPosition.objects.update_or_create(
                        account=account, ticket=ticket, defaults=vd
                    )
                    created += 1 if was_created else 1
                    updated += 0 if was_created else 1

                except Exception as e:
                    failed += 1
                    err_counter[("__exception__",)] += 1
                    errors.append({"index": idx, "error": {"__all__": [str(e)]}})
                    logger.exception("rid=%s open-positions.bulk.unexpected idx=%d", rid, idx)

        ok = failed == 0
        status_code = status.HTTP_201_CREATED if ok else status.HTTP_207_MULTI_STATUS
        error_summary = {"|".join(k) if isinstance(k, tuple) else str(k): v
                         for k, v in err_counter.items()}
        logger.info("rid=%s open-positions.bulk.done created=%d updated=%d failed=%d ok=%s",
                    rid, created, updated, failed, ok)
        return Response(
            {"ok": ok, "created": created, "updated": updated, "failed": failed,
             "errors": errors[:10], "error_summary": error_summary, "request_id": rid},
            status=status_code,
        )

    @action(detail=False, methods=["post"], url_path="replace")
    def replace(self, request):
        rid = getattr(request, "_request_id", "-")
        payload = request.data
        if not isinstance(payload, dict):
            return Response({"detail": "object required", "request_id": rid}, status=400)

        acc_id = payload.get("account_id")
        snapshot_ts = payload.get("snapshot_ts")
        items = payload.get("items", [])

        if not acc_id or not snapshot_ts or not isinstance(items, list):
            return Response({"detail": "account_id, snapshot_ts, items are required",
                             "request_id": rid}, status=400)

        try:
            account = _resolve_active_account(acc_id)
        except Exception as e:
            return Response({"detail": str(e), "request_id": rid}, status=400)

        objs = []
        for idx, raw in enumerate(items):
            s = self.get_serializer(data={**raw, "account_id": acc_id, "snapshot_ts": snapshot_ts})
            if not s.is_valid():
                return Response({"detail": "invalid item", "index": idx, "errors": s.errors,
                                 "request_id": rid}, status=400)
            vd = dict(s.validated_data)
            vd.pop("account_id", None)
            objs.append(OpenPosition(account=account, **vd))

        with transaction.atomic():
            OpenPosition.objects.filter(account=account).delete()
            OpenPosition.objects.bulk_create(objs)

        return Response({"ok": True, "count": len(objs), "request_id": rid}, status=201)

# --- Phantom Job 最小実装 ---
class PhantomJobViewSet(viewsets.ModelViewSet):
    """
    Phantom EAジョブ管理: フルCRUD + claim + complete
    """
    queryset = PhantomJob.objects.select_related("account").all().order_by("-created_at")
    serializer_class = PhantomJobSerializer
    permission_classes = [IsAuthenticated | HasAPIKey]

    @action(detail=False, methods=["post"], url_path="claim")
    def claim(self, request):
        """
        PENDING or RESUME を対象に 1件だけ RUNNING にして返す（ACK不要）
        body: {"account_id":"MT5-xxxx", "symbol":"EURUSD"}  # symbol必須
        戻り: 200 OK + job（RESUME時は runtime_params をフラット展開 + ネスト同梱）/ 204 No Content
        """
        account_id = request.data.get("account_id")
        symbol     = request.data.get("symbol")

        if not account_id:
            return Response({"detail": "account_id required"}, status=status.HTTP_400_BAD_REQUEST)
        if not symbol:
            return Response({"detail": "symbol required"}, status=status.HTTP_400_BAD_REQUEST)

        account = get_object_or_404(Account.objects.filter(is_active=True), account_id=account_id)

        with transaction.atomic():
            qs = (
                PhantomJob.objects
                .select_for_update(skip_locked=True)
                .filter(
                    account=account,
                    symbol=symbol,
                    status__in=[PhantomJob.Status.RESUME, PhantomJob.Status.PENDING],
                )
                .annotate(
                    status_pri=Case(
                        When(status=PhantomJob.Status.RESUME, then=0),
                        When(status=PhantomJob.Status.PENDING, then=1),
                        default=2,
                        output_field=IntegerField(),
                    )
                )
                .order_by("status_pri", "created_at")
            )

            job = qs.first()
            if not job:
                return Response(status=status.HTTP_204_NO_CONTENT)

            prev_status = job.status

            # RUNNING へ（ACK不要）
            job.status = PhantomJob.Status.RUNNING

            update_fields = ["status"]
            # PENDING -> RUNNING の時だけ started_at をセット（初回開始時刻保持）
            if prev_status == PhantomJob.Status.PENDING and not getattr(job, "started_at", None):
                job.started_at = timezone.now()
                update_fields.append("started_at")

            job.save(update_fields=update_fields)

        # 標準シリアライズ
        data = self.get_serializer(job).data

        # RESUME の場合は runtime_params をフラット展開して応答にマージ
        # ★ 空dictでも「存在」はTrueにしたいので None 判定を採用
        has_runtime_params = (getattr(job, "runtime_params", None) is not None)
        if prev_status == PhantomJob.Status.RESUME and has_runtime_params:
            rp = job.runtime_params or {}
            # EAが欲しいキーだけを安全に絞ってマージ（必要に応じて拡張）
            allow = {
                "planned_slots", "job_lot", "slippage",
                "tol_price_pips", "cooldown_sec", "max_lot_cap",
                "side", "sl_price", "tp_price", "symbol",
            }
            for k in allow:
                if k in rp:
                    data[k] = rp[k]

        # ★ RESUME の場合は、ネストの runtime_params もそのまま同梱（GET不要でも拾えるように）
        if prev_status == PhantomJob.Status.RESUME:
            data["runtime_params"] = job.runtime_params or {}

        # EA が分岐しやすい補助フィールドを追加
        data.update({
            "prev_status": prev_status,
            "resumed": (prev_status == PhantomJob.Status.RESUME),
            "has_runtime_params": has_runtime_params,
        })

        return Response(data, status=status.HTTP_200_OK)
        
    @action(detail=True, methods=["post"], url_path="runtime-params")
    def set_runtime_params(self, request, pk=None):
        job = self.get_object()
        # 空JSON {} も許容
        params = request.data if isinstance(request.data, dict) else {}
        job.runtime_params = params
        job.save(update_fields=["runtime_params"])
        return Response({"ok": True}, status=status.HTTP_200_OK)

    @action(detail=True, methods=["post", "patch"], url_path="complete")
    def complete(self, request, pk=None):
        job = self.get_object()
        if job.status != PhantomJob.Status.RUNNING:
            return Response({"detail": "invalid_state"}, status=status.HTTP_409_CONFLICT)
        job.status = PhantomJob.Status.COMPLETED
        job.finished_at = timezone.now()
        job.save(update_fields=["status", "finished_at"])
        return Response({"ok": True}, status=status.HTTP_200_OK)

    @action(detail=True, methods=["post", "patch"], url_path="error")
    def error(self, request, pk=None):
        job = self.get_object()
        if job.status not in (PhantomJob.Status.PENDING, PhantomJob.Status.RUNNING, PhantomJob.Status.RESUME):
            return Response({"detail": "invalid_state"}, status=status.HTTP_409_CONFLICT)
        job.status       = PhantomJob.Status.ERROR
        job.error_detail = request.data.get("error_detail") or job.error_detail
        job.failed_at    = timezone.now()
        job.save(update_fields=["status", "error_detail", "failed_at"])
        return Response({"ok": True}, status=status.HTTP_200_OK)

class QuickOrderForm(forms.Form):
    account = forms.ModelChoiceField(label="Account", queryset=Account.objects.all().order_by("account_id"))
    symbol  = forms.ChoiceField(label="Symbol", choices=SYMBOL_CHOICES)
    sl      = forms.FloatField(label="SL Price")
    tp      = forms.FloatField(label="TP Price")
    # risk_percent = forms.FloatField(label="Risk％（任意）", required=False, initial=3.5)

    # Target EMA
    target_ema = forms.ChoiceField(
        label="Target EMA (1H)",
        choices=[("", "— Select —")] + list(PhantomJob.TargetEMA.choices),
        required=True,
    )

    # シナリオURL
    scenario_url = forms.URLField(
        label="Scenario URL (Wiki permanent link)",
        required=True,
        widget=forms.URLInput(attrs={
            "placeholder": "https://portal.mshin0509.com/<your-page>",
            "inputmode": "url",
            "autocomplete": "url",
        })
    )

    # サブミット前チェックのフラグ（必須）
    confirm_po  = forms.BooleanField(
        label="Confirmed perfect order of 20 / 80 / 320 EMA on 1H timeframe.",
        required=True
    )
    confirm_scn = forms.BooleanField(
        label="Confirmed in the trade notes: scenario design including price zone analysis and trend analysis.",
        required=True
    )
    confirm_sltp  = forms.BooleanField(
        label="Confirmed that stop loss is placed at the price level where the setup is invalidated, and take profit is set at a reasonable, not overly ambitious level.",
        required=True
    )
    confirm_risk = forms.BooleanField(
        label="Confirmed: I accept the risk — anything can happen — and I will simply wait for the outcome after submission.",
        required=True
    )

    def clean(self):
        cleaned = super().clean()
        sl = cleaned.get("sl")
        tp = cleaned.get("tp")

        # === ① 入力チェック ===
        if sl is None or tp is None:
            self.add_error(None, "Both SL and TP are required.")

        if sl == tp:
            self.add_error("tp", "SL and TP values are the same. Please set a different distance between them.")

        # === ② side 判定（エラーがなければのみ設定）===
        if not self.errors:
            cleaned["side"] = "BUY" if sl < tp else "SELL"

        # === ③ 土日禁止チェック ===
        today_local = timezone.localdate()
        if today_local.weekday() >= 5:
            self.add_error(None, "Jobs cannot be submitted on weekends.")

        # === ④ 時間帯チェック（08:30〜22:30）===
        now_local = timezone.localtime()
        minutes = now_local.hour * 60 + now_local.minute
        if not (510 <= minutes <= 1350):
            self.add_error(None, "Jobs can be submitted between 08:30 and 22:30 JST.")

        # === ⑤ 1日1発 制約（JST基準のqueue_dateと揃える）===
        account = cleaned.get("account")
        if account:
            if PhantomJob.objects.filter(account=account, queue_date=today_local).exists():
                self.add_error(None, f"A job has already been queued today ({today_local}) for {account}.")

            # ⑥ 同一アカウントで RUNNING があれば Submit 禁止
            if PhantomJob.objects.filter(account=account, status=PhantomJob.Status.RUNNING).exists():
                self.add_error(None, f"{account} already has a RUNNING job. Please wait until it finishes.")

        # === ⑥ 必須チェック ===
        if not cleaned.get("confirm_po"):
            self.add_error("confirm_po", "Make sure to confirm the perfect order of 20 / 80 / 320 EMA on the 1H timeframe.")
        if not cleaned.get("confirm_scn"):
            self.add_error("confirm_scn", "Make sure to confirm in the trade notes: scenario design including price zone analysis and trend analysis.")
        if not cleaned.get("confirm_sltp"):
            self.add_error("confirm_sltp", "Make sure to place stop loss price where the setup is invalidated, and take profit price at a reasonable, not overly ambitious level.")
        if not cleaned.get("confirm_risk"):
            self.add_error("confirm_risk", "Make sure to accept the risk and wait for the outcome after submission.")

        return cleaned


TRADE_HINTS = [
    "Pursue positive expectancy and execute strictly by the rules.",
    "Do not judge expectancy from a handful of trades—evaluate over 100 trades.",
    "If 100 trades still yield negative expectancy, review the method and refine these hints.",
    "Primary setups are around the 1H 20EMA or 80EMA. 20EMA entries are prone to noise stops—decide whether to (a) place a slightly wider SL and wait for the pullback, (b) widen SL and extend TP accordingly, or (c) combine both.",
    "Be cautious with late-trend 1H 20EMA touches—the win rate degrades.",
    "Prefer entries near the Mid price. To enter from Mid, submit the job when price is between Mid and Q1. Avoid Q3 entries; they can produce a max drawdown pattern around −10R.",
    "While in a position, don’t stare at the charts. Stay relaxed—listen to chill music instead.",
    "Aim for a 100× total return over 30 years. This corresponds to an average CAGR of about 17%. It may not seem dramatic annually, but it compounds into something extraordinary over the long term.",
]

@method_decorator(staff_member_required, name="dispatch")
class QuickOrderView(FormView):
    template_name = "order.html"
    form_class = QuickOrderForm

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)

        # 最新ジョブ（参考表示）
        latest_job = PhantomJob.objects.order_by("-created_at").first() if PhantomJob.objects.exists() else None
        context["latest_job"] = latest_job

        # ★ OpenOrder / OpenPosition を全件そのまま渡す（アカウントで絞らない）
        open_orders = OpenOrder.objects.all().order_by("account__account_id", "-ticket")
        open_positions = OpenPosition.objects.all().order_by("account__account_id", "-ticket")
        context["open_orders"] = open_orders
        context["open_positions"] = open_positions

        # ★ snapshot_ts はどちらかのテーブルの先頭1件から拾う（全件同一を前提）
        snapshot_ts = (
            open_orders.values_list("snapshot_ts", flat=True).first()
            or open_positions.values_list("snapshot_ts", flat=True).first()
        )
        context["snapshot_ts"] = snapshot_ts

        context["trade_hints"] = TRADE_HINTS

        return context

    def form_valid(self, form):
        account = form.cleaned_data["account"]
        symbol  = form.cleaned_data["symbol"]
        sl      = form.cleaned_data["sl"]
        tp      = form.cleaned_data["tp"]
        RISK_PERCENT_DEFAULT = 3.5  # Risk% Fixed
        side = "BUY" if sl < tp else "SELL"
        target_ema  = form.cleaned_data.get("target_ema") or None
        scenario_url = form.cleaned_data.get("scenario_url") or None


        try:
            with transaction.atomic():
                job = PhantomJob(
                    account=account,
                    symbol=symbol,
                    side=side,
                    sl_price=sl,
                    tp_price=tp,
                    use_risk_lot=True,
                    risk_percent=RISK_PERCENT_DEFAULT,
                    status=PhantomJob.Status.PENDING,
                    target_ema=target_ema,
                    scenario_url=scenario_url,
                )
                job.save()
        except ValidationError as e:
            for field, msgs in e.message_dict.items():
                if field in form.fields:
                    for msg in msgs:
                        form.add_error(field, msg)
                else:
                    for msg in msgs:
                        form.add_error(None, msg)
            return self.form_invalid(form)

        messages.success(
            self.request,
            f"PhantomJob is submitted (Account: {account}, Symbol: {symbol}, Side: {side}, SL: {sl}, TP: {tp}, Use Risk: True, Risk Percent: {RISK_PERCENT_DEFAULT}, Target EMA: {target_ema}, URL: {scenario_url})."
        )
        # 送信後は画面に戻る（アカウントは絞っていないため、単純リダイレクト）
        return redirect(reverse("order"))