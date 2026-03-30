from dataclasses import dataclass


@dataclass(frozen=True)
class RuntimePlan:
    watchlist_id: int
    available_strategy_keys: list[str]
    globally_enabled_strategy_keys: list[str]
    assigned_strategy_keys: list[str]
    enabled_strategy_keys: list[str]
