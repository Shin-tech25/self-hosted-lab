import os, requests
from collections import Counter
import logging
from datetime import timezone as dt_tz
from django import forms
from django.views.generic import FormView
from django.contrib import messages
from django.contrib.admin.views.decorators import staff_member_required
from django.utils.dateparse import parse_date, parse_datetime
from django.shortcuts import redirect, get_object_or_404
from rest_framework import serializers, viewsets, mixins, status
from rest_framework.response import Response
from rest_framework.decorators import action
from rest_framework.views import APIView
from rest_framework.permissions import IsAuthenticated
from rest_framework_api_key.permissions import HasAPIKey
from rest_framework.filters import OrderingFilter, SearchFilter
from django_filters.rest_framework import DjangoFilterBackend

from django.db import transaction
from django.utils import timezone
from django.utils.decorators import method_decorator

from .models import (
    Account,
    AccountDailyStat,
    ClosedPosition,
    PhantomJob,
)
from .serializers import (
    PhantomJobSerializer,
    AccountDailyStatSerializer,
    ClosedPositionSerializer,
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
        直近5件のPENDINGから、(account_id, symbol) 一致の最も古い1件を RUNNING にして返す
        body: {"account_id":"MT5-xxxx", "symbol":"EURUSD"}  # symbolはEA側が自チャートを必ず渡す
        戻り: 200 OK + job / 204 No Content（該当なし）
        """
        account_id = request.data.get("account_id")
        symbol     = request.data.get("symbol")

        if not account_id:
            return Response({"detail": "account_id required"}, status=status.HTTP_400_BAD_REQUEST)
        if not symbol:
            # 今後の運用では必須。互換のため警告返すこともできるが、ここでは400にする方が安全。
            return Response({"detail": "symbol required"}, status=status.HTTP_400_BAD_REQUEST)

        # 口座ID文字列 -> Account（FK）解決
        account = get_object_or_404(Account.objects.filter(is_active=True), account_id=account_id)
        if not account:
            return Response({"detail": f"account not found: {account_id}"}, status=status.HTTP_404_NOT_FOUND)

        with transaction.atomic():
            # 直近5件をロックしてからsymbol一致を最古で選択
            base_qs = (
                PhantomJob.objects
                .select_for_update(skip_locked=True)
                .filter(status=PhantomJob.Status.PENDING, account=account)
                .order_by("-created_at")[:5]
            )
            # slice 後の追加フィルタのため list 化
            candidates = [j for j in base_qs if j.symbol == symbol]
            candidates.sort(key=lambda j: j.created_at)

            job = candidates[0] if candidates else None
            if not job:
                return Response({"detail": "no_pending_job"}, status=status.HTTP_204_NO_CONTENT)

            job.status = PhantomJob.Status.RUNNING
            job.started_at = timezone.now()
            job.save()

        return Response(self.get_serializer(job).data, status=status.HTTP_200_OK)

    @action(detail=True, methods=["post", "patch"], url_path="complete")
    def complete(self, request, pk=None):
        job = self.get_object()
        if job.status != PhantomJob.Status.RUNNING:
            return Response({"detail": "invalid_state"}, status=status.HTTP_409_CONFLICT)
        job.status = PhantomJob.Status.COMPLETED
        job.finished_at = timezone.now()
        job.save()
        return Response({"ok": True}, status=status.HTTP_200_OK)

    @action(detail=True, methods=["post", "patch"], url_path="error")
    def error(self, request, pk=None):
        job = self.get_object()
        if job.status not in (PhantomJob.Status.PENDING, PhantomJob.Status.RUNNING):
            return Response({"detail": "invalid_state"}, status=status.HTTP_409_CONFLICT)
        job.status       = PhantomJob.Status.ERROR
        job.error_detail = request.data.get("error_detail") or job.error_detail
        job.failed_at    = timezone.now()
        job.save()
        return Response({"ok": True}, status=status.HTTP_200_OK)

class QuickOrderForm(forms.Form):
    account = forms.ModelChoiceField(label="口座", queryset=Account.objects.all().order_by("account_id"))
    symbol  = forms.ChoiceField(label="通貨ペア", choices=SYMBOL_CHOICES)
    sl      = forms.FloatField(label="SL（損切り）")
    tp      = forms.FloatField(label="TP（利確）")
    #risk_percent = forms.FloatField(label="Risk％（任意）", required=False, initial=3.5)

    def clean(self):
        cleaned = super().clean()
        sl = cleaned.get("sl")
        tp = cleaned.get("tp")

        # ① どちらか未入力ならエラー
        if sl is None or tp is None:
            self.add_error(None, "SLとTPの両方を入力してください。")
            return cleaned  # side 判定はスキップ
        # ② 同値ならエラー
        if sl == tp:
            self.add_error("tp", "SLとTPが同値です。どちらかを離してください。")
            return cleaned
        # ③ side 判定（有効時のみ）
        cleaned["side"] = "BUY" if sl < tp else "SELL"
        return cleaned


@method_decorator(staff_member_required, name="dispatch")
class QuickOrderView(FormView):
    template_name = "order.html"
    form_class = QuickOrderForm

    def form_valid(self, form):
        account = form.cleaned_data["account"]
        symbol  = form.cleaned_data["symbol"]
        sl      = form.cleaned_data["sl"]
        tp      = form.cleaned_data["tp"]
        RISK_PERCENT_DEFAULT = 3.5  # Risk% Fixed
        #risk_percent   = form.cleaned_data["risk_percent"]

        side = "BUY" if sl < tp else "SELL"

        job = PhantomJob.objects.create(
            account=account,
            symbol=symbol,
            side=side,
            sl_price=sl,
            tp_price=tp,
            use_risk_lot=True,
            risk_percent=RISK_PERCENT_DEFAULT,
            status=PhantomJob.Status.PENDING,
        )
        messages.success(self.request, f"発注ジョブを作成しました（{side} {symbol}）")
        return redirect("order")