from dataclasses import dataclass


@dataclass(frozen=True)
class ScoreWeights:
    trend_alignment: float = 0.30
    confidence: float = 0.25
    volume_confirmation: float = 0.15
    volatility_quality: float = 0.10
    structure_quality: float = 0.20
