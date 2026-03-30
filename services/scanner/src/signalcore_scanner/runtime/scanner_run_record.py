from dataclasses import dataclass, field

from signalcore_scanner.runtime.scanner_run_error import ScannerRunError


@dataclass(frozen=True)
class ScannerRunRecord:
    watchlist_id: int
    timeframe: str
    status: str
    started_at: str
    completed_at: str | None = None
    symbols_scanned_count: int = 0
    strategies_executed_count: int = 0
    signals_found_count: int = 0
    error_count: int = 0
    metadata: dict[str, float | int | str | bool] = field(default_factory=dict)
    errors: tuple[ScannerRunError, ...] = ()
