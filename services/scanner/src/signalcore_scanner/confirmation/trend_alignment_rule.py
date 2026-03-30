class TrendAlignmentRule:
    def score(self, direction: str, higher_timeframe_bias: str | None) -> float:
        if higher_timeframe_bias is None or higher_timeframe_bias == 'neutral':
            return 50.0

        if direction == 'bullish' and higher_timeframe_bias == 'bullish':
            return 100.0
        if direction == 'bearish' and higher_timeframe_bias == 'bearish':
            return 100.0

        return 0.0
