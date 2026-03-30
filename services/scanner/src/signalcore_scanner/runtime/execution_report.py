from dataclasses import dataclass, field

from signalcore_scanner.contracts.scanner_run_output import ScannerStrategyResult
from signalcore_scanner.runtime.execution_lifecycle_state import ExecutionLifecycleState


@dataclass(frozen=True)
class ExecutionReport:
    strategy_results: tuple[ScannerStrategyResult, ...]
    lifecycle: tuple[ExecutionLifecycleState, ...]
    failures: tuple[ExecutionLifecycleState, ...] = field(default_factory=tuple)
