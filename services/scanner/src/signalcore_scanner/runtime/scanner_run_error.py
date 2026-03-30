from dataclasses import dataclass, field


@dataclass(frozen=True)
class ScannerRunError:
    strategy_key: str | None = None
    symbol: str | None = None
    message: str | None = None
    diagnostics: dict[str, float | int | str | bool] = field(default_factory=dict)
