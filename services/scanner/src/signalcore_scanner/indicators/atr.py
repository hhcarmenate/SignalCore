def average_true_range(highs: list[float], lows: list[float], closes: list[float], period: int = 14) -> float | None:
    if period <= 0 or len(highs) != len(lows) or len(highs) != len(closes) or len(highs) <= period:
        return None

    true_ranges: list[float] = []
    previous_close = closes[0]

    for high, low, close in zip(highs[1:], lows[1:], closes[1:]):
        true_range = max(
            high - low,
            abs(high - previous_close),
            abs(low - previous_close),
        )
        true_ranges.append(true_range)
        previous_close = close

    atr = sum(true_ranges[:period]) / period

    for true_range in true_ranges[period:]:
        atr = ((atr * (period - 1)) + true_range) / period

    return round(atr, 4)
