from statistics import fmean


def simple_moving_average(values: list[float], period: int) -> float | None:
    if period <= 0 or len(values) < period:
        return None

    return round(fmean(values[-period:]), 4)
