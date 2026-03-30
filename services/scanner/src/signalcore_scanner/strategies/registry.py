from signalcore_scanner.contracts.strategy_definition import StrategyDefinition


TREND_CONTINUATION = StrategyDefinition(
    key="trend_continuation",
    name="Trend Continuation",
    description="Follow established trend continuation setups across supported timeframes.",
)

BREAKOUT_CONFIRMATION = StrategyDefinition(
    key="breakout_confirmation",
    name="Breakout Confirmation",
    description="Validate breakout or breakdown conditions before producing a signal.",
)

MEAN_REVERSION_TO_TREND = StrategyDefinition(
    key="mean_reversion_to_trend",
    name="Mean Reversion to Trend",
    description="Detect pullbacks that mean revert back into the prevailing trend.",
)

DEFAULT_STRATEGIES = {
    strategy.key: strategy
    for strategy in [
        TREND_CONTINUATION,
        BREAKOUT_CONFIRMATION,
        MEAN_REVERSION_TO_TREND,
    ]
}
