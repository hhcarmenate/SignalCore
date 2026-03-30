from dataclasses import dataclass


@dataclass(frozen=True)
class SignalScoreInput:
    confidence: float
    trend_alignment: float
    volume_confirmation: float
    volatility_quality: float
    structure_quality: float
