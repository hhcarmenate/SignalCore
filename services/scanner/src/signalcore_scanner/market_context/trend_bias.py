from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.indicators.indicator_snapshot import IndicatorSnapshot


class TrendBiasAnalyzer:
    def detect(self, candles: tuple[CandlePoint, ...], indicators: IndicatorSnapshot) -> str:
        if not candles or indicators.close is None:
            return 'neutral'

        if indicators.ema_20 and indicators.ema_50 and indicators.close > indicators.ema_20 > indicators.ema_50:
            return 'bullish'

        if indicators.ema_20 and indicators.ema_50 and indicators.close < indicators.ema_20 < indicators.ema_50:
            return 'bearish'

        return 'neutral'
