from dataclasses import dataclass


@dataclass(frozen=True)
class StrategyState:
    strategy_key: str
    is_enabled: bool
