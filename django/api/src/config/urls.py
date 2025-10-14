from django.contrib import admin
from django.urls import path, include
from rest_framework.routers import DefaultRouter
from drf_spectacular.views import SpectacularAPIView, SpectacularSwaggerView
from django.contrib.admin.views.decorators import staff_member_required
from core.views import QuickOrderView
from django.views.generic import RedirectView
from django.templatetags.static import static

from core.views import (
    HealthzView,
    AccountDailyStatViewSet,
    ClosedPositionViewSet,
    OpenOrderViewSet,
    OpenPositionViewSet,
    PhantomJobViewSet,
)

router = DefaultRouter()
router.register(r"account-daily-stats", AccountDailyStatViewSet, basename="account-daily-stats")
router.register(r"closed-positions",   ClosedPositionViewSet,    basename="closed-positions")
router.register(r"open-orders",   OpenOrderViewSet,    basename="open-orders")
router.register(r"open-positions",   OpenPositionViewSet,    basename="open-positions")
router.register(r"phantom-jobs", PhantomJobViewSet, basename="phantom-jobs")

urlpatterns = [
    path("forex/admin/", admin.site.urls),
    path("forex/api/", include(router.urls)),
    path("forex/healthz", HealthzView.as_view()),
    path("forex/order", QuickOrderView.as_view(), name="order"),
    # path("forex/favicon.ico", RedirectView.as_view(
    #     url=static("favicon.png"), permanent=True
    # )),
]

admin.site.site_url = None
