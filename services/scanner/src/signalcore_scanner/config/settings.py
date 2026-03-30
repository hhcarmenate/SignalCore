from dataclasses import dataclass
from typing import Mapping


@dataclass(frozen=True)
class ScannerServiceConfig:
    database_dsn: str
    default_watchlist_id: int | None = None
    scanner_batch_size: int = 100
    allow_disabled_strategies: bool = False
    extra: Mapping[str, str] | None = None
