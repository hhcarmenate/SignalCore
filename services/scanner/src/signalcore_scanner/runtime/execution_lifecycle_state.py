from dataclasses import dataclass, field


@dataclass(frozen=True)
class ExecutionLifecycleState:
    status: str
    watchlist_id: int
    timeframe: str
    strategy_key: str | None = None
    symbol: str | None = None
    message: str | None = None
    diagnostics: dict[str, float | int | str | bool] = field(default_factory=dict)
