from rest_framework import serializers
from .models import Account, AccountDailyStat, ClosedPosition, OpenOrder, OpenPosition, PhantomJob

# ========== 共通: account_id -> Account 解決 ==========
def _resolve_active_account(account_id: str) -> Account:
    try:
        return Account.objects.get(account_id=account_id, is_active=True)
    except Account.DoesNotExist:
        raise serializers.ValidationError({"account_id": "active account not found"})


# ========== AccountDailyStat ==========
class AccountDailyStatSerializer(serializers.ModelSerializer):
    # 書き込みは account_id、読み出しは account の account_id を返す
    account_id = serializers.CharField(write_only=True, required=True)
    account    = serializers.SlugRelatedField(read_only=True, slug_field="account_id")

    class Meta:
        model  = AccountDailyStat
        fields = ("id", "account_id", "account", "date", "balance", "equity", "pnl", "created_at")
        read_only_fields = ("id", "account", "created_at")

    def create(self, validated):
        acc_id = validated.pop("account_id")
        acc    = _resolve_active_account(acc_id)

        # 冪等：同一 (account, date) は upsert
        obj, _ = AccountDailyStat.objects.update_or_create(
            account=acc,
            date=validated["date"],
            defaults=validated,
        )
        return obj

    def update(self, instance, validated):
        # 必要なら account を差し替え可
        if "account_id" in validated:
            instance.account = _resolve_active_account(validated.pop("account_id"))
        return super().update(instance, validated)


# ========== ClosedPosition ==========
class ClosedPositionSerializer(serializers.ModelSerializer):
    account_id  = serializers.CharField(write_only=True, required=True)
    account     = serializers.SlugRelatedField(read_only=True, slug_field="account_id")

    class Meta:
        model  = ClosedPosition
        fields = (
            "id", "account_id", "account",
            "ticket",
            "symbol", "side",
            "open_price", "close_price", "volume",
            "open_time", "close_time",
            "profit", "commission", "swap",
            "net_profit",
            "magic", "comment",
            "created_at",
        )
        read_only_fields = ("id", "account", "net_profit", "created_at")

    def validate(self, attrs):
        ot = attrs.get("open_time")
        ct = attrs.get("close_time")
        if ot and ct and ct < ot:
            raise serializers.ValidationError("close_time は open_time 以降である必要があります。")
        if self.instance is None and not attrs.get("ticket"):
            raise serializers.ValidationError({"ticket": "required"})
        return attrs

    def create(self, validated):
        # 冪等UPSERT: (account, ticket) をキーに update_or_create
        acc_id = validated.pop("account_id")
        account = _resolve_active_account(acc_id)
        ticket  = validated.pop("ticket", None)
        if ticket is None:
            raise serializers.ValidationError({"ticket": "required"})

        # account/ticket 以外は defaults としてまとめる
        defaults = validated
        obj, _ = ClosedPosition.objects.update_or_create(
            account=account, ticket=ticket, defaults=defaults
        )
        return obj

    def update(self, instance, validated):
        # account_id の差し替えに対応（通常は不要だが一応）
        if "account_id" in validated:
            instance.account = _resolve_active_account(validated.pop("account_id"))
        # ticket は通常固定にすべき（履歴の同一性を壊すため）→ 受け取っても無視する運用が安全
        validated.pop("ticket", None)
        return super().update(instance, validated)

# ========== OpenOrder ==========
class OpenOrderSerializer(serializers.ModelSerializer):
    account_id = serializers.CharField(write_only=True)

    class Meta:
        model = OpenOrder
        fields = [
            "account_id", "ticket", "symbol", "side", "otype", "volume",
            "price", "sl", "tp", "magic", "comment", "placed_at", "expires_at",
            "snapshot_ts",
        ]

    def create(self, validated):
        acc_id = validated.pop("account_id")
        account = _resolve_active_account(acc_id)
        return OpenOrder.objects.create(account=account, **validated)

# ========== OpenPosition ==========
class OpenPositionSerializer(serializers.ModelSerializer):
    account_id = serializers.CharField(write_only=True)

    class Meta:
        model = OpenPosition
        fields = [
            "account_id", "ticket", "symbol", "side", "volume",
            "open_price", "sl", "tp", "magic", "comment", "open_time",
            "snapshot_ts",
        ]

    def create(self, validated):
        acc_id = validated.pop("account_id")
        account = _resolve_active_account(acc_id)
        return OpenPosition.objects.create(account=account, **validated)

# ========== PhantomJob ==========
class PhantomJobSerializer(serializers.ModelSerializer):
    # 書き込み：account_id を受け取る
    account_id = serializers.CharField(write_only=True, required=True)

    # 読み出し：account の account_id と name を付加
    account      = serializers.SlugRelatedField(read_only=True, slug_field="account_id")
    account_name = serializers.CharField(source="account.name", read_only=True)
    side = serializers.ChoiceField(choices=PhantomJob.Side.choices)

    class Meta:
        model  = PhantomJob
        fields = [
            "id",
            "account_id", "account", "account_name",
            "symbol",
            "side",
            "sl_price", "tp_price",
            "use_risk_lot", "risk_percent",
            "lots_fixed", "max_lot_cap",
            "slippage", "tol_price_pips", "cooldown_sec",
            "status",
            "magic",
            "created_at", "updated_at", "started_at", "finished_at",
            "error_detail", "failed_at",
        ]
        read_only_fields = [
            "id",
            "account", "account_name",
            "magic",
            "created_at", "updated_at", "started_at", "finished_at", "failed_at",
        ]

    def create(self, validated):
        acc_id = validated.pop("account_id", None)
        if not acc_id:
            raise serializers.ValidationError({"account_id": "required"})
        validated["account"] = _resolve_active_account(acc_id)
        return super().create(validated)

    def update(self, instance, validated):
        acc_id = validated.pop("account_id", None)
        if acc_id is not None:
            instance.account = _resolve_active_account(acc_id)
        return super().update(instance, validated)
