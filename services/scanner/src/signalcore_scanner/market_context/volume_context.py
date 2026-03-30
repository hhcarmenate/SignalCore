from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.indicators.indicator_snapshot import IndicatorSnapshot


class VolumeContextAnalyzer:
    def detect(self, candles: tuple[CandlePoint, ...], indicators: IndicatorSnapshot) -> str:
        if not candles or indicators.volume_sma_20 is None:
            return 'unknown'

        latest_volume = float(candles[-1].volume)

        if latest_volume >= indicators.volume_sma_20 * 1.2:
            return 'expanding'
        if latest_volume <= indicators.volume_sma_20 * 0.8:
            return 'contracting'

        return 'normal'
