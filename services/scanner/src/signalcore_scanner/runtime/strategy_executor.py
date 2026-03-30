from typing import Protocol

from signalcore_scanner.contracts.scanner_run_output import ScannerStrategyResult
from signalcore_scanner.contracts.strategy_execution_input import StrategyExecutionInput


class StrategyExecutor(Protocol):
    def execute(self, execution_input: StrategyExecutionInput) -> ScannerStrategyResult: ...
