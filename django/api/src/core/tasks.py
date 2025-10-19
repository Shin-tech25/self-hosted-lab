# core/tasks.py
import datetime
import logging
from celery import shared_task
from django.db import transaction
from django.utils import timezone

from core.models import PhantomJob

logger = logging.getLogger(__name__)

@shared_task
def hello_task():
    msg = f"[{datetime.datetime.now()}] Hello World"
    print(msg)
    logger.info(msg)
    return msg

@shared_task
def force_complete_running_jobs(note: str = "weekly-cutoff"):
    """
    毎週土曜 04:00(JST) の“持ち越し禁止”で、
    RUNNING の PhantomJob を COMPLETED に強制更新する。
    RESUME は対象外。

    Returns:
        dict: {"matched": int, "updated": int, "ts": str, "note": str}
    """
    qs = PhantomJob.objects.filter(status=PhantomJob.Status.RUNNING)
    matched = qs.count()

    with transaction.atomic():
        # completed_at 等のタイムスタンプ列がある場合は、以下のように一括更新OK
        update_kwargs = {"status": PhantomJob.Status.COMPLETED}
        # 例: if hasattr(PhantomJob, "completed_at"): update_kwargs["completed_at"] = timezone.now()
        updated = qs.update(**update_kwargs)

    return {
        "matched": matched,
        "updated": updated,
        "ts": timezone.now().isoformat(),
        "note": note,
    }