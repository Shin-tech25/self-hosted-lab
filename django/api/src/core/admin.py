from django.contrib import admin
from rangefilter.filters import DateRangeFilter, DateTimeRangeFilter
from .models import Account, AccountDailyStat, ClosedPosition, PhantomJob

@admin.register(Account)
class AccountAdmin(admin.ModelAdmin):
    list_display   = ("account_id", "broker", "name", "server", "is_active", "created_at")
    list_filter    = ("broker", "is_active")
    search_fields  = ("account_id", "name", "server")
    ordering       = ("-is_active", "broker", "account_id")

@admin.register(AccountDailyStat)
class AccountDailyStatAdmin(admin.ModelAdmin):
    list_display = ("id", "account", "date", "balance", "equity", "pnl", "created_at")
    list_filter = (
        ("date", DateRangeFilter),
        "account",
    )
    date_hierarchy = "date"
    ordering = ("-date", "-id")
    readonly_fields = ("created_at",)
    list_per_page = 50

@admin.register(ClosedPosition)
class ClosedPositionAdmin(admin.ModelAdmin):
    # 一覧に「最終損益（net）」と ticket / commission / swap を追加
    list_display = (
        "id",
        "account_id_display",  # 口座IDで直感的に
        "symbol", "side", "volume",
        "open_price", "close_price",
        "profit", "commission", "swap",
        "net_profit_display",  # 見やすい最終損益
        "ticket", "magic",
        "open_time", "close_time",
        "created_at",
    )
    list_filter = (
        ('close_time', DateTimeRangeFilter),
        'account',
        'symbol', 'side',
    )
    search_fields = (
        "=magic",
    )
    search_help_text = "Search by Magic Number"
    date_hierarchy = "close_time"
    ordering = ("-close_time", "-id")
    readonly_fields = ("created_at",)
    list_select_related = ("account",)  # N+1回避
    autocomplete_fields = ("account",)  # 口座が多い場合に便利
    list_per_page = 50

    @admin.display(ordering="account__account_id", description="Account ID")
    def account_id_display(self, obj):
        return obj.account.account_id

    def net_profit_display(self, obj):
        # 見やすくゼロ/符号付きで
        if obj.net_profit is None:
            return "-"
        # 小数2桁、符号付き
        return f"{obj.net_profit:+,.2f}"

@admin.register(PhantomJob)
class PhantomJobAdmin(admin.ModelAdmin):
    list_display = (
        "id", "status", "account_id", "symbol", "side", "sl_price", "tp_price",
        "use_risk_lot", "risk_percent", "lots_fixed", "max_lot_cap",
        "slippage", "tol_price_pips", "cooldown_sec",
        "magic", "started_at", "finished_at", "error_detail", "failed_at", "created_at",
    )
    list_filter = (
        ("finished_at", DateTimeRangeFilter),
        "account_id", "status", "symbol", "side",
    )
    search_fields = ("=magic",)
    search_help_text = "Search by Magic Number"
    ordering = ("-created_at",)
    date_hierarchy = "created_at"
    readonly_fields = ("id", "created_at", "updated_at",)

