# core/tasks.py
from celery import shared_task
import datetime
import logging

logger = logging.getLogger(__name__)

@shared_task
def hello_task():
    msg = f"[{datetime.datetime.now()}] Hello World"
    print(msg)
    logger.info(msg)
    return msg
