def clamp_score(value: float) -> float:
    return round(max(0.0, min(100.0, value)), 4)
