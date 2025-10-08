# utils/magic.py
import os
import time

def generate_magic():
    t = time.gmtime()
    year = max(0, min(127, t.tm_year - 2000))  # 0..127 (2000-2127想定)
    day  = t.tm_yday                            # 1..366
    hour = t.tm_hour                            # 0..23
    # 10bit のシーケンス（0..1023）。本番はDBの排他カウンタ推奨。
    seq  = int.from_bytes(os.urandom(2), "big") & 0x3FF

    magic = (year << 24) | (day << 15) | (hour << 10) | seq
    return int(magic)  # 最大 2,147,450,879 → MT4のint範囲内
