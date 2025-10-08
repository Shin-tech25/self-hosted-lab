import logging
from rest_framework.views import exception_handler as drf_exception_handler

logger = logging.getLogger("api.exception")

def custom_exception_handler(exc, context):
    response = drf_exception_handler(exc, context)
    request = context.get("request")
    rid = getattr(request, "_request_id", "-")

    if response is not None:
        try:
            preview = request.body[:2048].decode("utf-8", errors="replace")
        except Exception:
            preview = "<unavailable>"

        logger.warning("rid=%s exc=%s status=%s path=%s body=%s",
                       rid, exc.__class__.__name__, response.status_code,
                       request.path, preview)
        # レスポンスにも request_id を混ぜる（既存 body を崩さない）
        if isinstance(response.data, dict) and "request_id" not in response.data:
            response.data["request_id"] = rid

    return response
