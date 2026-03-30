from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.strategy_execution_input import MarketContextSnapshot, SwingLevels
from signalcore_scanner.indicators.indicator_snapshot import IndicatorSnapshot
from signalcore_scanner.market_context.regime import RegimeClassifier
from signalcore_scanner.market_context.swing_levels import SwingLevelDetector
from signalcore_scanner.market_context.trend_analysis_result import TrendAnalysisResult
from signalcore_scanner.market_context.trend_bias import TrendBiasAnalyzer
from signalcore_scanner.market_context.volume_context import VolumeContextAnalyzer


class MarketContextAnalyzer:
    def __init__(self) -> None:
        self.trend_bias_analyzer = TrendBiasAnalyzer()
        self.swing_level_detector = SwingLevelDetector()
        self.regime_classifier = RegimeClassifier()
        self.volume_context_analyzer = VolumeContextAnalyzer()

    def build(self, candles: tuple[CandlePoint, ...], indicators: IndicatorSnapshot) -> TrendAnalysisResult:
        trend_bias = self.trend_bias_analyzer.detect(candles, indicators)
        swing_high, swing_low = self.swing_level_detector.detect(candles)
        regime, volatility_state = self.regime_classifier.classify(indicators)
        volume_state = self.volume_context_analyzer.detect(candles, indicators)

        notes: list[str] = []
        if swing_high is not None and indicators.close is not None and indicators.close >= swing_high:
            notes.append('price_at_or_above_swing_high')
        if swing_low is not None and indicators.close is not None and indicators.close <= swing_low:
            notes.append('price_at_or_below_swing_low')

        return TrendAnalysisResult(
            context=MarketContextSnapshot(
                trend_bias=trend_bias,
                higher_timeframe_bias=trend_bias,
                regime=regime,
                volatility_state=volatility_state,
                volume_state=volume_state,
                swing_levels=SwingLevels(
                    swing_high=swing_high,
                    swing_low=swing_low,
                    breakout_level=swing_high,
                    breakdown_level=swing_low,
                ),
                notes=tuple(notes),
            )
        )
