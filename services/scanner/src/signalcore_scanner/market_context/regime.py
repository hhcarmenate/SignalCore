from signalcore_scanner.indicators.indicator_snapshot import IndicatorSnapshot


class RegimeClassifier:
    def classify(self, indicators: IndicatorSnapshot) -> tuple[str, str]:
        if indicators.atr_14 is None or indicators.close is None or indicators.volume_sma_20 is None:
            return 'unknown', 'unknown'

        volatility_ratio = indicators.atr_14 / indicators.close if indicators.close else 0

        if volatility_ratio >= 0.03:
            volatility_state = 'high'
        elif volatility_ratio <= 0.01:
            volatility_state = 'low'
        else:
            volatility_state = 'normal'

        if indicators.rsi_14 is None:
            regime = 'balanced'
        elif indicators.rsi_14 >= 60:
            regime = 'trend'
        elif indicators.rsi_14 <= 40:
            regime = 'pullback'
        else:
            regime = 'balanced'

        return regime, volatility_state
