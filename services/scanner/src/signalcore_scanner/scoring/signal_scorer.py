from signalcore_scanner.contracts.scanner_signal_output import SignalScoreBreakdown
from signalcore_scanner.contracts.signal_score_input import SignalScoreInput
from signalcore_scanner.scoring.normalization import clamp_score
from signalcore_scanner.scoring.score_weights import ScoreWeights


class SignalScorer:
    def __init__(self, weights: ScoreWeights | None = None) -> None:
        self.weights = weights or ScoreWeights()

    def score(self, score_input: SignalScoreInput) -> SignalScoreBreakdown:
        trend_alignment = clamp_score(score_input.trend_alignment * self.weights.trend_alignment)
        confidence = clamp_score(score_input.confidence * self.weights.confidence)
        volume_confirmation = clamp_score(score_input.volume_confirmation * self.weights.volume_confirmation)
        volatility_quality = clamp_score(score_input.volatility_quality * self.weights.volatility_quality)
        structure_quality = clamp_score(score_input.structure_quality * self.weights.structure_quality)
        composite = clamp_score(
            trend_alignment
            + confidence
            + volume_confirmation
            + volatility_quality
            + structure_quality
        )

        return SignalScoreBreakdown(
            trend_alignment=trend_alignment,
            confidence=confidence,
            volume_confirmation=volume_confirmation,
            volatility_quality=volatility_quality,
            structure_quality=structure_quality,
            composite=composite,
        )
