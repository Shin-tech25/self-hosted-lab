# src/core/utils/symbols.py

# === 通貨ペア定数 ===
SYMBOL_CHOICES = [
    ("USDJPY", "USDJPY"),
    ("EURUSD", "EURUSD"),
    ("EURJPY", "EURJPY"),
    ("GBPJPY", "GBPJPY"),
    ("GBPUSD", "GBPUSD"),
    ("AUDJPY", "AUDJPY"),
    ("EURGBP", "EURGBP"),
    ("USDCHF", "USDCHF"),
    ("USDJPY#", "USDJPY#"),
    ("EURUSD#", "EURUSD#"),
    ("EURJPY#", "EURJPY#"),
    ("GBPJPY#", "GBPJPY#"),
    ("GBPUSD#", "GBPUSD#"),
    ("AUDJPY#", "AUDJPY#"),
    ("EURGBP#", "EURGBP#"),
    ("USDCHF#", "USDCHF#"),
]

# === 値リストだけ欲しい場合 ===
SYMBOL_VALUES = [v for v, _ in SYMBOL_CHOICES]
