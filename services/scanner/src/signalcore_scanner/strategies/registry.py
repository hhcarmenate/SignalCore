from signalcore_scanner.contracts.strategy_definition import StrategyDefinition


TREND_CONTINUATION = StrategyDefinition(
    key='trend_continuation',
    name='Trend Continuation',
    description='Follow established trend continuation setups across supported timeframes.',
    priority=1,
    directional_biases=('bullish', 'bearish'),
    execution_hints=('call', 'put'),
    notes=(
        'Core MVP strategy',
        'Best suited for established directional conditions',
    ),
)

BREAKOUT_CONFIRMATION = StrategyDefinition(
    key='breakout_confirmation',
    name='Breakout Confirmation',
    description='Validate breakout or breakdown conditions before producing a signal.',
    priority=2,
    directional_biases=('bullish', 'bearish'),
    execution_hints=('call', 'put'),
    notes=(
        'Core MVP strategy',
        'Requires confirmation through price structure and context',
    ),
)

MEAN_REVERSION_TO_TREND = StrategyDefinition(
    key='mean_reversion_to_trend',
    name='Mean Reversion to Trend',
    description='Detect pullbacks that mean revert back into the prevailing trend.',
    priority=3,
    directional_biases=('bullish', 'bearish'),
    execution_hints=('call', 'put'),
    notes=(
        'Core MVP strategy',
        'Designed for pullback entries aligned with the broader trend',
    ),
)

MVP_STRATEGIES = (
    TREND_CONTINUATION,
    BREAKOUT_CONFIRMATION,
    MEAN_REVERSION_TO_TREND,
)

DEFAULT_STRATEGIES = {
    strategy.key: strategy
    for strategy in MVP_STRATEGIES
}

EXCLUDED_FROM_MVP = {
    'counter_trend_reversal': 'Excluded from MVP to avoid fighting the dominant market context too early.',
    'range_rotation': 'Excluded from MVP until range/regime handling is more mature.',
    'volatility_expansion_scalp': 'Excluded from MVP because it would increase execution complexity and noise.',
}
