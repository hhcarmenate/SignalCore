from dataclasses import dataclass

from signalcore_scanner.runtime.execution_target import ExecutionTarget


@dataclass(frozen=True)
class ExecutionBatch:
    targets: tuple[ExecutionTarget, ...]
