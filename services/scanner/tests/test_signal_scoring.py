import unittest

from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput
from signalcore_scanner.contracts.signal_score_input import SignalScoreInput
from signalcore_scanner.scoring import SignalRanker, SignalScorer


class SignalScoringTest(unittest.TestCase):
    def test_signal_scorer_builds_weighted_breakdown(self) -> None:
        breakdown = SignalScorer().score(
            SignalScoreInput(
                confidence=80,
                trend_alignment=90,
                volume_confirmation=70,
                volatility_quality=60,
                structure_quality=75,
            )
        )

        self.assertEqual(27.0, breakdown.trend_alignment)
        self.assertEqual(20.0, breakdown.confidence)
        self.assertEqual(10.5, breakdown.volume_confirmation)
        self.assertEqual(6.0, breakdown.volatility_quality)
        self.assertEqual(15.0, breakdown.structure_quality)
        self.assertEqual(78.5, breakdown.composite)

    def test_signal_ranker_orders_by_ranking_score_then_confidence(self) -> None:
        signals = (
            ScannerSignalOutput(
                strategy_key='trend_continuation',
                symbol='AAPL',
                timeframe='4h',
                direction='bullish',
                thesis='A',
                confidence=70,
                score=70,
                ranking_score=72,
                signal_category='trend_continuation',
            ),
            ScannerSignalOutput(
                strategy_key='breakout_confirmation',
                symbol='NVDA',
                timeframe='4h',
                direction='bullish',
                thesis='B',
                confidence=80,
                score=70,
                ranking_score=72,
                signal_category='breakout_confirmation',
            ),
            ScannerSignalOutput(
                strategy_key='mean_reversion_to_trend',
                symbol='QQQ',
                timeframe='4h',
                direction='bullish',
                thesis='C',
                confidence=60,
                score=68,
                ranking_score=68,
                signal_category='mean_reversion_to_trend',
            ),
        )

        ranked = SignalRanker().rank(signals)

        self.assertEqual(['NVDA', 'AAPL', 'QQQ'], [signal.symbol for signal in ranked])
        self.assertEqual([1, 2, 3], [signal.ranking_position for signal in ranked])

    def test_signal_payload_includes_scoring_fields(self) -> None:
        breakdown = SignalScorer().score(
            SignalScoreInput(
                confidence=80,
                trend_alignment=90,
                volume_confirmation=70,
                volatility_quality=60,
                structure_quality=75,
            )
        )
        signal = ScannerSignalOutput(
            strategy_key='trend_continuation',
            symbol='SPY',
            timeframe='1d',
            direction='bullish',
            thesis='Payload test',
            confidence=80,
            score=78.5,
            ranking_score=78.5,
            ranking_position=1,
            score_breakdown=breakdown,
            signal_category='trend_continuation',
        )

        payload = signal.to_payload()

        self.assertEqual(78.5, payload['ranking_score'])
        self.assertEqual(1, payload['ranking_position'])
        self.assertEqual(78.5, payload['score_breakdown']['composite'])


if __name__ == '__main__':
    unittest.main()
