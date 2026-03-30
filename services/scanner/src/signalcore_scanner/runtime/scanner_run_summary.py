from dataclasses import dataclass


@dataclass(frozen=True)
class ScannerRunSummary:
    status: str
    symbols_scanned_count: int
    strategies_executed_count: int
    signals_found_count: int
    error_count: int
