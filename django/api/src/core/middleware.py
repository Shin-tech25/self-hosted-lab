import json, uuid, logging
from django.utils.deprecation import MiddlewareMixin

logger = logging.getLogger("api.request")

MAX_LOG_BYTES = 4096
MASK_HEADERS  = {"authorization", "cookie"}

def _mask_headers(headers: dict) -> dict:
    masked = {}
    for k, v in headers.items():
        if k.lower() in MASK_HEADERS:
            masked[k] = "***"
        else:
            masked[k] = v
    return masked

class RequestLogMiddleware(MiddlewareMixin):
    def process_request(self, request):
        # 相関ID
        rid = request.META.get("HTTP_X_REQUEST_ID") or str(uuid.uuid4())
        request._request_id = rid

        # ヘッダ（危険なものはマスク）
        headers = {k[5:].replace("_", "-"): v for k, v in request.META.items() if k.startswith("HTTP_")}
        headers = _mask_headers(headers)

        # ボディ（バイナリ/巨大は抑制）
        body_preview = b""
        try:
            body_preview = request.body[:MAX_LOG_BYTES]
        except Exception:
            pass

        try:
            body_txt = body_preview.decode("utf-8", errors="replace")
        except Exception:
            body_txt = "<bin>"

        logger.info(
            "rid=%s method=%s path=%s ip=%s ct=%s cl=%s headers=%s body=%s%s",
            rid,
            request.method,
            request.path,
            request.META.get("REMOTE_ADDR"),
            request.META.get("CONTENT_TYPE"),
            request.META.get("CONTENT_LENGTH"),
            headers,
            body_txt,
            " ...truncated" if (request.META.get("CONTENT_LENGTH") and int(request.META["CONTENT_LENGTH"]) > MAX_LOG_BYTES) else "",
        )

    def process_response(self, request, response):
        rid = getattr(request, "_request_id", None)
        if rid:
            response["X-Request-ID"] = rid
        return response
