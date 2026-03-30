class ConflictFilter:
    def detect(self, direction: str, trigger_bias: str | None, higher_timeframe_bias: str | None) -> bool:
        if trigger_bias is None or higher_timeframe_bias is None:
            return False

        if direction == 'bullish' and ('bearish' in {trigger_bias, higher_timeframe_bias}):
            return True
        if direction == 'bearish' and ('bullish' in {trigger_bias, higher_timeframe_bias}):
            return True

        return False
