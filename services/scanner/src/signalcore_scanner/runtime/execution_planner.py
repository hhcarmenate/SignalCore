from math import ceil

from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol
from signalcore_scanner.runtime.execution_batch import ExecutionBatch
from signalcore_scanner.runtime.execution_plan import ExecutionPlan
from signalcore_scanner.runtime.execution_target import ExecutionTarget
from signalcore_scanner.runtime.runtime_plan import RuntimePlan


class ExecutionPlanner:
    def build(
        self,
        runtime_plan: RuntimePlan,
        symbols: list[WatchlistSymbol],
    ) -> ExecutionPlan:
        targets = [
            ExecutionTarget(
                watchlist_id=runtime_plan.watchlist_id,
                timeframe=runtime_plan.timeframe or 'unknown',
                strategy_key=strategy_key,
                symbol=symbol,
            )
            for strategy_key in runtime_plan.enabled_strategy_keys
            for symbol in symbols
        ]

        if not targets:
            return ExecutionPlan(runtime_plan=runtime_plan, batches=())

        batch_size = max(runtime_plan.batch_size, 1)
        batch_count = ceil(len(targets) / batch_size)
        batches = tuple(
            ExecutionBatch(targets=tuple(targets[index * batch_size:(index + 1) * batch_size]))
            for index in range(batch_count)
        )

        return ExecutionPlan(runtime_plan=runtime_plan, batches=batches)
