from signalcore_scanner.confirmation.conflict_filter import ConflictFilter
from signalcore_scanner.confirmation.trend_alignment_rule import TrendAlignmentRule
from signalcore_scanner.contracts.multi_timeframe_confirmation_input import MultiTimeframeConfirmationInput
from signalcore_scanner.contracts.multi_timeframe_confirmation_result import MultiTimeframeConfirmationResult


class MultiTimeframeConfirmer:
    def __init__(self) -> None:
        self.trend_alignment_rule = TrendAlignmentRule()
        self.conflict_filter = ConflictFilter()

    def confirm(self, confirmation_input: MultiTimeframeConfirmationInput) -> MultiTimeframeConfirmationResult:
        trigger_bias = confirmation_input.trigger_context.trend_bias or confirmation_input.trigger_context.market_context.trend_bias
        higher_timeframe_bias = (
            confirmation_input.higher_timeframe_context.trend_bias
            or confirmation_input.higher_timeframe_context.market_context.higher_timeframe_bias
            or confirmation_input.higher_timeframe_context.market_context.trend_bias
        )

        alignment_score = self.trend_alignment_rule.score(
            confirmation_input.direction,
            higher_timeframe_bias,
        )
        conflict_detected = self.conflict_filter.detect(
            confirmation_input.direction,
            trigger_bias,
            higher_timeframe_bias,
        )
        passed_minimum_threshold = confirmation_input.trigger_score >= confirmation_input.minimum_score_threshold

        notes: list[str] = []
        if alignment_score == 100.0:
            notes.append('higher_timeframe_aligned')
        elif alignment_score == 50.0:
            notes.append('higher_timeframe_neutral')
        else:
            notes.append('higher_timeframe_misaligned')

        if conflict_detected:
            notes.append('timeframe_conflict_detected')
        if not passed_minimum_threshold:
            notes.append('below_minimum_quality_threshold')

        is_confirmed = alignment_score > 0 and not conflict_detected and passed_minimum_threshold

        return MultiTimeframeConfirmationResult(
            is_confirmed=is_confirmed,
            alignment_score=alignment_score,
            conflict_detected=conflict_detected,
            passed_minimum_threshold=passed_minimum_threshold,
            notes=tuple(notes),
        )
