from dataclasses import dataclass, field


@dataclass(frozen=True)
class RuntimePlan:
    watchlist_id: int
    available_strategy_keys: list[str]
    globally_enabled_strategy_keys: list[str]
    assigned_strategy_keys: list[str]
    enabled_strategy_keys: list[str]
    timeframe: str | None = None
    batch_size: int = 50
    symbol_ids: list[int] = field(default_factory=list)
