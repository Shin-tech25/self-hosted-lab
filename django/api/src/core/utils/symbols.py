# src/core/utils/symbols.py

# === 通貨ペア定数 ===
SYMBOL_CHOICES = [
    ("USDJPY.oj1m", "USDJPY.oj1m"),
    ("EURUSD.oj1m", "EURUSD.oj1m"),
    ("EURJPY.oj1m", "EURJPY.oj1m"),
    ("GBPJPY.oj1m", "GBPJPY.oj1m"),
    ("GBPUSD.oj1m", "GBPUSD.oj1m"),
    ("AUDJPY.oj1m", "AUDJPY.oj1m"),
    ("AUDUSD.oj1m", "AUDUSD.oj1m"),
    ("NZDJPY.oj1m", "NZDJPY.oj1m"),
    ("EURGBP.oj1m", "EURGBP.oj1m"),
    ("USDCHF.oj1m", "USDCHF.oj1m"),
    ("USDCAD.oj1m", "USDCAD.oj1m"),
    ("USDJPY", "USDJPY"),
    ("EURUSD", "EURUSD"),
    ("EURJPY", "EURJPY"),
    ("GBPJPY", "GBPJPY"),
    ("GBPUSD", "GBPUSD"),
    ("AUDJPY", "AUDJPY"),
    ("AUDUSD", "AUDUSD"),
    ("NZDJPY", "NZDJPY"),
    ("EURGBP", "EURGBP"),
    ("USDCHF", "USDCHF"),
    ("USDCAD", "USDCAD"),
    ("USDJPY#", "USDJPY#"),
    ("EURUSD#", "EURUSD#"),
    ("EURJPY#", "EURJPY#"),
    ("GBPJPY#", "GBPJPY#"),
    ("GBPUSD#", "GBPUSD#"),
    ("AUDJPY#", "AUDJPY#"),
    ("AUDUSD#", "AUDUSD#"),
    ("NZDJPY#", "NZDJPY#"),
    ("EURGBP#", "EURGBP#"),
    ("USDCHF#", "USDCHF#"),
    ("USDCAD#", "USDCAD#"),
]

# === 値リストだけ欲しい場合 ===
SYMBOL_VALUES = [v for v, _ in SYMBOL_CHOICES]
