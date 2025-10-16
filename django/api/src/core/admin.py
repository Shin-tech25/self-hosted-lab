from django.contrib import admin, messages
from django.http import HttpResponse
from django.template.loader import render_to_string
from django.utils.timezone import now
from rangefilter.filters import DateRangeFilter, DateTimeRangeFilter
from .models import Account, AccountDailyStat, ClosedPosition, OpenOrder, OpenPosition, PhantomJob

from .utils.perf import summarize

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
    
    actions = ["export_perf_html", "export_perf_pdf"]

    def export_perf_html(self, request, queryset):
        if not queryset.exists():
            self.message_user(request, "対象データがありません。", level=messages.WARNING)
            return
        queryset = queryset.select_related("account").order_by("close_time", "id")
        summary = summarize(queryset)
        html = render_to_string("admin/perf_report.html", {
            "summary": summary,
            "generated_at": now(),
            "count": queryset.count(),
            "trades": queryset,  # ← 明細を渡す
        })
        return HttpResponse(html, content_type="text/html; charset=utf-8")
    export_perf_html.short_description = "Export Performance Report for Selected Trades"

    # def export_perf_pdf(self, request, queryset):
    #     queryset = queryset.select_related("account").order_by("close_time", "id")
    #     if not queryset.exists():
    #         self.message_user(request, "対象データがありません。", level=messages.WARNING)
    #         return
    #     summary = summarize(queryset)
    #     html = render_to_string("admin/perf_report.html", {
    #         "summary": summary,
    #         "generated_at": now(),
    #         "count": queryset.count(),
    #         "trades": queryset,  # ← 明細を渡す
    #         "for_pdf": True,
    #     })
    #     # WeasyPrintでPDF化（要: weasyprint インストール）
    #     from weasyprint import HTML
    #     pdf = HTML(string=html, base_url=request.build_absolute_uri("/")).write_pdf()
    #     resp = HttpResponse(pdf, content_type="application/pdf")
    #     resp["Content-Disposition"] = 'attachment; filename="perf_report.pdf"'
    #     return resp
    # export_perf_pdf.short_description = "選択した決済のパフォーマンスレポート（PDFダウンロード）"

@admin.register(OpenOrder)
class OpenOrderAdmin(admin.ModelAdmin):
    list_display  = (
        "account", "symbol", "side", "otype",
        "ticket", "volume", "sl", "tp",
        "magic", "comment", "placed_at", "expires_at", "snapshot_ts",
    )
    list_filter   = (
        "account", "symbol", "side", "otype",
        ("snapshot_ts", DateTimeRangeFilter),
    )
    search_fields = ("account__account_id", "symbol", "comment", "ticket", "magic")
    ordering      = ("-snapshot_ts", "-ticket")
    date_hierarchy = "snapshot_ts"
    list_per_page = 50
    readonly_fields = (
        "account", "ticket", "symbol", "side", "otype", "volume",
        "price", "sl", "tp", "magic", "comment",
        "placed_at", "expires_at", "snapshot_ts",
    )

    fieldsets = (
        (None, {
            "fields": (
                ("account", "snapshot_ts"),
                ("symbol", "side", "otype"),
                ("ticket", "volume", "magic"),
                ("sl", "tp"),
                "comment",
                ("placed_at", "expires_at"),
                "price",
            )
        }),
    )


@admin.register(OpenPosition)
class OpenPositionAdmin(admin.ModelAdmin):
    list_display  = (
        "account", "symbol", "side",
        "ticket", "volume", "sl", "tp",
        "magic", "comment", "open_time", "snapshot_ts",
    )
    list_filter   = (
        "account", "symbol", "side",
        ("snapshot_ts", DateTimeRangeFilter),
    )
    search_fields = ("account__account_id", "symbol", "comment", "ticket", "magic")
    ordering      = ("-snapshot_ts", "-ticket")
    date_hierarchy = "snapshot_ts"
    list_per_page = 50
    readonly_fields = (
        "account", "ticket", "symbol", "side", "volume",
        "open_price", "sl", "tp", "magic", "comment",
        "open_time", "snapshot_ts",
    )

    fieldsets = (
        (None, {
            "fields": (
                ("account", "snapshot_ts"),
                ("symbol", "side"),
                ("ticket", "volume", "magic"),
                ("sl", "tp"),
                "comment",
                "open_time",
                "open_price",
            )
        }),
    )

@admin.register(PhantomJob)
class PhantomJobAdmin(admin.ModelAdmin):
    list_display = (
        "id", "status", "account_id", "symbol", "side", "sl_price", "tp_price",
        "use_risk_lot", "risk_percent", "lots_fixed", "max_lot_cap",
        "slippage", "tol_price_pips", "cooldown_sec",
        "magic", "queue_date", "queue_time_display", "started_at", "finished_at",
        "error_detail", "failed_at", "scenario_url", "target_ema", "total_pnl",
        "created_at",
    )
    list_filter = (
        ("finished_at", DateTimeRangeFilter),
        "account_id", "symbol", "target_ema", "status", "side",
    )
    search_fields = ("=magic",)
    search_help_text = "Search by Magic Number"
    ordering = ("-created_at",)
    date_hierarchy = "created_at"
    readonly_fields = (
        "id", "created_at", "updated_at",
        # "status",
    )

    def queue_time_display(self, obj):
        m = obj.queue_minutes or 0
        return f"{m//60:02d}:{m%60:02d} JST"
    queue_time_display.short_description = "Queued (JST)"