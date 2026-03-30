from dataclasses import dataclass

from signalcore_scanner.runtime.execution_batch import ExecutionBatch
from signalcore_scanner.runtime.runtime_plan import RuntimePlan


@dataclass(frozen=True)
class ExecutionPlan:
    runtime_plan: RuntimePlan
    batches: tuple[ExecutionBatch, ...]
