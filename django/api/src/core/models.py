import uuid
from django.core.exceptions import ValidationError
from django.db import models, IntegrityError, transaction
from django.db.models import Q
from django.utils import timezone
from .utils.magic import generate_magic
from .utils.symbols import SYMBOL_CHOICES

class Account(models.Model):
    class Broker(models.TextChoices):
        OANDA   = "OANDA", "OANDA"
        XM      = "XM", "XM"

    id = models.BigAutoField(primary_key=True)
    account_id = models.CharField(max_length=64, unique=True, db_index=True)  # 例: "23839934"
    broker     = models.CharField(max_length=16, choices=Broker.choices, default=Broker.XM)
    name       = models.CharField(max_length=64, blank=True)  # 表示用ラベル（任意）
    server     = models.CharField(max_length=64, blank=True)  # 例: "OANDA-v20-Tokyo"
    is_active  = models.BooleanField(default=True)
    note       = models.TextField(blank=True)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        ordering = ["-is_active", "broker", "account_id"]

    def __str__(self):
        return self.name or f"{self.broker}:{self.account_id}"

class AccountDailyStat(models.Model):
    id = models.BigAutoField(primary_key=True)
    account = models.ForeignKey(Account, on_delete=models.CASCADE, related_name="daily_stats")
    date = models.DateField(db_index=True)
    balance = models.DecimalField(max_digits=18, decimal_places=2)
    equity = models.DecimalField(max_digits=18, decimal_places=2)  # 有効証拠金
    pnl = models.DecimalField(max_digits=18, decimal_places=2)     # 日次損益（PnL）

    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = "account_daily_stats"
        constraints = [
            models.UniqueConstraint(fields=["account", "date"], name="uniq_account_date"),
        ]
        indexes = [
            models.Index(fields=["account", "date"], name="idx_account_date"),
        ]
        ordering = ["-date"]

    def __str__(self):
        return f"{self.account.account_id} {self.date}"

class ClosedPosition(models.Model):
    id = models.BigAutoField(primary_key=True)

    account = models.ForeignKey(
        Account, on_delete=models.CASCADE, related_name="closed_positions"
    )

    # MT4/MT5 履歴のユニーク識別子（差分同期とUPSERTの要）
    ticket = models.BigIntegerField(default=0, db_index=True)

    symbol = models.CharField(max_length=20)
    side   = models.CharField(max_length=4, choices=[("BUY", "Buy"), ("SELL", "Sell")])

    # 価格・数量（Decimalで厳密に）
    open_price  = models.DecimalField(max_digits=18, decimal_places=5)
    close_price = models.DecimalField(max_digits=18, decimal_places=5)
    volume      = models.DecimalField(max_digits=12, decimal_places=2)

    # 時刻
    open_time  = models.DateTimeField()
    close_time = models.DateTimeField(db_index=True)

    # 収益分解（口座通貨建て）
    # ※ MT4: OrderProfit() は売買差益、Commission()/Swap() が別
    profit     = models.DecimalField(max_digits=18, decimal_places=2)  # ポジション差益
    commission = models.DecimalField(max_digits=18, decimal_places=2, default=0)  # 手数料
    swap       = models.DecimalField(max_digits=18, decimal_places=2, default=0)  # スワップ

    magic   = models.BigIntegerField(db_index=True, null=True, blank=True)
    comment = models.CharField(max_length=255, blank=True)

    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table  = "closed_positions"
        ordering  = ["-close_time", "-id"]
        constraints = [
            # 差分同期のための一意制約
            models.UniqueConstraint(
                fields=["account", "ticket"], name="uniq_closedpos_account_ticket"
            ),
        ]
        indexes = [
            # 差分取得と検索用インデックス
            models.Index(fields=["account", "close_time", "ticket"]),
            models.Index(fields=["account", "symbol", "close_time"]),
            models.Index(fields=["magic"]),
        ]

    def __str__(self):
        # 最終損益（手数料・スワップ込み）を見たいときは下の @property を使う
        return f"{self.account.account_id} {self.symbol} {self.side} {self.net_profit:+}"

    @property
    def net_profit(self):
        # 最終損益 = 売買差益 + 手数料 + スワップ
        return (self.profit or 0) + (self.commission or 0) + (self.swap or 0)

class PhantomJob(models.Model):
    class Side(models.TextChoices):
        BUY = "BUY", "Buy"
        SELL = "SELL", "Sell"

    class Status(models.TextChoices):
        PENDING   = "PENDING"
        RUNNING   = "RUNNING"
        COMPLETED = "COMPLETED"
        ERROR     = "ERROR"
    
    id = models.BigAutoField(primary_key=True)
    magic = models.BigIntegerField(default=generate_magic, unique=True, db_index=True)

    account = models.ForeignKey("Account", on_delete=models.PROTECT,
                                related_name="phantom_jobs", null=True, blank=True, db_index=True)
    
    symbol = models.CharField(
        max_length=32,
        db_index=True,
        choices=SYMBOL_CHOICES,
        default=SYMBOL_CHOICES[0][0],
    )

    side = models.CharField(
        max_length=4,
        choices=Side.choices,
        default=Side.BUY,
    )
    sl_price      = models.FloatField(null=True, blank=True)
    tp_price      = models.FloatField(null=True, blank=True)
    use_risk_lot  = models.BooleanField(default=True)
    risk_percent  = models.FloatField(default=2.0)
    lots_fixed    = models.FloatField(default=0.10)
    max_lot_cap   = models.FloatField(default=10.0)
    slippage      = models.IntegerField(default=3)
    tol_price_pips= models.FloatField(default=2.5)
    cooldown_sec  = models.IntegerField(default=8)

    status      = models.CharField(max_length=12, choices=Status.choices,
                                   default=Status.PENDING, db_index=True)
    started_at  = models.DateTimeField(null=True, blank=True)
    finished_at = models.DateTimeField(null=True, blank=True)

    error_detail = models.TextField(null=True, blank=True)
    failed_at    = models.DateTimeField(null=True, blank=True)

    created_at  = models.DateTimeField(auto_now_add=True)
    updated_at  = models.DateTimeField(auto_now=True)

    class Meta:
        constraints = [
            # PENDING のときは started_at/finished_at は NULL でなければならない
            models.CheckConstraint(
                name="phantomjob_pending_requires_null_times",
                check=(
                    (Q(status="PENDING") & Q(started_at__isnull=True) & Q(finished_at__isnull=True))
                    | ~Q(status="PENDING")
                ),
            ),
            # COMPLETED のときは finished_at が必須（あるいは claim/complete で必ず埋める運用）
            # models.CheckConstraint(
            #     name="phantomjob_completed_requires_finished_at",
            #     check=(Q(status="COMPLETED") & Q(finished_at__isnull=False)) | ~Q(status="COMPLETED"),
            # ),
            # # ERRORなら failed_at 必須
            # models.CheckConstraint(
            #     name="phantomjob_error_requires_failed_at",
            #     check=(Q(status="ERROR") & Q(failed_at__isnull=False)) | ~Q(status="ERROR"),
            # ),
        ]

    # ---- モデルレベルの状態遷移ガード（Admin等も含め横断的に効かせる） ----
    def clean(self):
        super().clean()
        # 既存レコードなら「以前の状態」を取得
        if self.pk:
            prev = PhantomJob.objects.only("status").get(pk=self.pk).status
        else:
            prev = PhantomJob.Status.PENDING

        new = self.status

        allowed = {
            PhantomJob.Status.PENDING:   {PhantomJob.Status.PENDING, PhantomJob.Status.RUNNING, PhantomJob.Status.ERROR},
            PhantomJob.Status.RUNNING:   {PhantomJob.Status.PENDING, PhantomJob.Status.RUNNING, PhantomJob.Status.COMPLETED, PhantomJob.Status.ERROR},
            PhantomJob.Status.COMPLETED: {PhantomJob.Status.PENDING, PhantomJob.Status.COMPLETED},
            PhantomJob.Status.ERROR:     {PhantomJob.Status.PENDING, PhantomJob.Status.ERROR},
        }
        if new not in allowed[prev]:
            raise ValidationError({"status": f"{prev} から {new} への遷移は禁止です。"})
        # 追加整合性（保険）
        if new == PhantomJob.Status.PENDING and (self.started_at or self.finished_at):
            raise ValidationError({"status": "PENDING のとき started_at/finished_at は NULL である必要があります。"})

    def save(self, *args, **kwargs):
        """
        - RUNNING → COMPLETED を Admin から保存した場合でも落ちないよう、
          COMPLETED かつ finished_at が未設定ならサーバ時刻で自動セット。
        - 既に finished_at が入っている場合はそのまま（上書きしない）。
        """
        # COMPLETED で finished_at 自動補完
        if self.status == PhantomJob.Status.COMPLETED and self.finished_at is None:
            self.finished_at = timezone.now()
        # ERROR で failed_at 自動補完
        if self.status == PhantomJob.Status.ERROR and self.failed_at is None:
            self.failed_at = timezone.now()
        self.full_clean()  # 制約を守った状態で保存
        return super().save(*args, **kwargs)