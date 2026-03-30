from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput


class SignalRanker:
    def rank(self, signals: tuple[ScannerSignalOutput, ...]) -> tuple[ScannerSignalOutput, ...]:
        ordered = sorted(
            signals,
            key=lambda signal: (
                signal.ranking_score if signal.ranking_score is not None else signal.score,
                signal.confidence,
            ),
            reverse=True,
        )

        return tuple(
            ScannerSignalOutput(
                strategy_key=signal.strategy_key,
                symbol=signal.symbol,
                timeframe=signal.timeframe,
                direction=signal.direction,
                thesis=signal.thesis,
                confidence=signal.confidence,
                score=signal.score,
                signal_category=signal.signal_category,
                execution_hint=signal.execution_hint,
                ranking_score=signal.ranking_score if signal.ranking_score is not None else signal.score,
                ranking_position=index,
                score_breakdown=signal.score_breakdown,
                levels=signal.levels,
                indicators=signal.indicators,
                context=signal.context,
                metadata=signal.metadata,
            )
            for index, signal in enumerate(ordered, start=1)
        )
