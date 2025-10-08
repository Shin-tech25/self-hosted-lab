import os, sys
from pathlib import Path
from dotenv import load_dotenv, find_dotenv

# --- load .env ---
load_dotenv(find_dotenv())  # 近い上位まで探索して .env を読み込む

BASE_DIR = Path(__file__).resolve().parent.parent

# ---- helpers ----
def env_bool(name: str, default: bool = False) -> bool:
    val = os.getenv(name)
    if val is None:
        return default
    return val.strip().lower() in ("1", "true", "yes", "on")

def env_list(name: str, default: str = "") -> list[str]:
    raw = os.getenv(name, default)
    return [x.strip() for x in raw.split(",") if x.strip()]

def env_str(name: str, default: str | None = None) -> str:
    v = os.getenv(name, default)
    if v is None:
        raise RuntimeError(f"Required environment variable '{name}' is not set")
    return v

# ---- core settings ----
SECRET_KEY = env_str("DJANGO_SECRET_KEY", "insecure")  # 本番は未設定にして強制
DEBUG = env_bool("DJANGO_DEBUG", default=True)  # 本番は False を想定

ALLOWED_HOSTS = env_list("DJANGO_ALLOWED_HOSTS", "localhost,127.0.0.1")
SECURE_PROXY_SSL_HEADER = ('HTTP_X_FORWARDED_PROTO', 'https')
USE_X_FORWARDED_HOST = True

CORS_ALLOWED_ORIGINS = env_list("DJANGO_CORS_ALLOWED_ORIGINS", "")
CSRF_TRUSTED_ORIGINS = env_list("DJANGO_CSRF_TRUSTED_ORIGINS", "")

DJANGO_LOG_LEVEL = env_str("DJANGO_LOG_LEVEL", "INFO")
DJANGO_DB_LOG_LEVEL = env_str("DJANGO_DB_LOG_LEVEL", "WARNING")

# 静的ファイル
STATIC_URL = "/static/"
STATIC_ROOT = BASE_DIR / "staticfiles"

INSTALLED_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
    "rest_framework",
    "django_filters",
    "corsheaders",
    "drf_spectacular",
    "core",
    "rest_framework_api_key",
]

MIDDLEWARE = [
    "core.middleware.RequestLogMiddleware", # API Request Logger
    "django.middleware.security.SecurityMiddleware",
    "whitenoise.middleware.WhiteNoiseMiddleware",
    "corsheaders.middleware.CorsMiddleware",
    "django.contrib.sessions.middleware.SessionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
    "django.contrib.auth.middleware.AuthenticationMiddleware",
    "django.contrib.messages.middleware.MessageMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
]

REST_FRAMEWORK = {
    "DEFAULT_AUTHENTICATION_CLASSES": [
        "rest_framework.authentication.SessionAuthentication",
    ],
    "DEFAULT_PERMISSION_CLASSES": [
        "rest_framework.permissions.IsAuthenticated",
    ],
    "DEFAULT_PAGINATION_CLASS": "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 50,
    "DEFAULT_FILTER_BACKENDS": (
        "django_filters.rest_framework.DjangoFilterBackend",
        "rest_framework.filters.SearchFilter",
        "rest_framework.filters.OrderingFilter",
    ),
    "DEFAULT_SCHEMA_CLASS": "drf_spectacular.openapi.AutoSchema",
    "EXCEPTION_HANDLER": "core.exceptions.custom_exception_handler",
}

SPECTACULAR_SETTINGS = {
    "TITLE": "MT5 Gateway API",
    "VERSION": "1.0.0",
    "SERVE_INCLUDE_SCHEMA": False,
    "AUTHENTICATION_SOURCES": ["drf_spectacular.contrib.rest_framework_api_key"],
    "SECURITY": [{"ApiKeyAuth": []}, {"cookieAuth": []}],
}

STATICFILES_STORAGE = "whitenoise.storage.CompressedManifestStaticFilesStorage"

ROOT_URLCONF = "config.urls"
WSGI_APPLICATION = "config.wsgi.application"
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [os.path.join(BASE_DIR, 'templates')],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]

LOGGING = {
    "version": 1,
    "disable_existing_loggers": False,
    "formatters": {
        "verbose": {
            "format": "%(asctime)s [%(levelname)s] %(name)s: %(message)s "
                      "(%(pathname)s:%(lineno)d pid=%(process)d tid=%(thread)d)"
        },
    },
    "handlers": {
        # systemd は stdout/stderr を journald が拾う
        "stdout": {"class": "logging.StreamHandler", "stream": sys.stdout, "formatter": "verbose"},
        "stderr": {"class": "logging.StreamHandler", "stream": sys.stderr, "formatter": "verbose"},
    },
    "root": {
        "handlers": ["stdout"],
        "level": DJANGO_LOG_LEVEL,
    },
    "loggers": {
        # 500系はここに来る（DEBUG=Falseでもトレース出力）
        "django.request": {"handlers": ["stderr"], "level": "ERROR", "propagate": False},
        # runserver 用（本番では影響小）
        "django.server": {"handlers": ["stdout"], "level": "INFO", "propagate": False},
        "django": {"handlers": ["stdout"], "level": DJANGO_LOG_LEVEL, "propagate": True},
        "django.db.backends": {"handlers": ["stdout"], "level": DJANGO_DB_LOG_LEVEL, "propagate": False},
        "rest_framework": {"handlers": ["stdout"], "level": "INFO", "propagate": False},
        "core": {"handlers": ["stdout"], "level": DJANGO_LOG_LEVEL, "propagate": False},
        # gunicorn ログを journal に流す
        "gunicorn.error": {"handlers": ["stderr"], "level": "INFO", "propagate": False},
        "gunicorn.access": {"handlers": ["stdout"], "level": "INFO", "propagate": False},
    },
}

# ---- database (MySQL) ----
DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.mysql",
        "NAME": os.getenv("MYSQL_DATABASE", "mt5_gateway"),
        "USER": os.getenv("MYSQL_USER", "mt5-user"),
        # 本番は必須化したい場合は env_str("MYSQL_PASSWORD") にする
        "PASSWORD": os.getenv("MYSQL_PASSWORD", ""),
        "HOST": os.getenv("MYSQL_HOST", "127.0.0.1"),
        "PORT": os.getenv("MYSQL_PORT", "3306"),
        "OPTIONS": {
            "charset": "utf8mb4",
            "init_command": "SET sql_mode='STRICT_TRANS_TABLES'",
        },
    }
}

LANGUAGE_CODE = "ja"
TIME_ZONE = "Asia/Tokyo"
USE_I18N = True
USE_TZ = True