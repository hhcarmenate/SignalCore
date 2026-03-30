from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.scanner_run_output import ScannerStrategyResult, build_strategy_result
from signalcore_scanner.contracts.strategy_execution_input import StrategyExecutionInput
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol
from signalcore_scanner.runtime.execution_lifecycle_state import ExecutionLifecycleState
from signalcore_scanner.runtime.execution_plan import ExecutionPlan
from signalcore_scanner.runtime.execution_report import ExecutionReport
from signalcore_scanner.runtime.strategy_executor import StrategyExecutor


class ScannerExecutionEngine:
    def __init__(self, strategy_executor: StrategyExecutor) -> None:
        self.strategy_executor = strategy_executor

    def execute(self, plan: ExecutionPlan) -> ExecutionReport:
        strategy_results: list[ScannerStrategyResult] = []
        lifecycle: list[ExecutionLifecycleState] = [
            ExecutionLifecycleState(
                status='started',
                watchlist_id=plan.runtime_plan.watchlist_id,
                timeframe=plan.runtime_plan.timeframe or 'unknown',
                diagnostics={'batch_count': len(plan.batches)},
            )
        ]
        failures: list[ExecutionLifecycleState] = []

        for batch_index, batch in enumerate(plan.batches, start=1):
            lifecycle.append(
                ExecutionLifecycleState(
                    status='batch_started',
                    watchlist_id=plan.runtime_plan.watchlist_id,
                    timeframe=plan.runtime_plan.timeframe or 'unknown',
                    message=f'batch_{batch_index}',
                    diagnostics={'target_count': len(batch.targets)},
                )
            )

            for target in batch.targets:
                lifecycle.append(
                    ExecutionLifecycleState(
                        status='target_started',
                        watchlist_id=target.watchlist_id,
                        timeframe=target.timeframe,
                        strategy_key=target.strategy_key,
                        symbol=target.symbol.symbol,
                    )
                )

                try:
                    result = self.strategy_executor.execute(
                        StrategyExecutionInput(
                            strategy_key=target.strategy_key,
                            watchlist_id=target.watchlist_id,
                            symbol=target.symbol,
                            timeframe=target.timeframe,
                            candles=(),
                        )
                    )
                except Exception as error:
                    failure = ExecutionLifecycleState(
                        status='target_failed',
                        watchlist_id=target.watchlist_id,
                        timeframe=target.timeframe,
                        strategy_key=target.strategy_key,
                        symbol=target.symbol.symbol,
                        message=str(error),
                    )
                    lifecycle.append(failure)
                    failures.append(failure)
                    continue

                strategy_results.append(result)
                lifecycle.append(
                    ExecutionLifecycleState(
                        status='target_completed',
                        watchlist_id=target.watchlist_id,
                        timeframe=target.timeframe,
                        strategy_key=target.strategy_key,
                        symbol=target.symbol.symbol,
                        diagnostics={'produced_signal': result.produced_signal},
                    )
                )

        lifecycle.append(
            ExecutionLifecycleState(
                status='completed',
                watchlist_id=plan.runtime_plan.watchlist_id,
                timeframe=plan.runtime_plan.timeframe or 'unknown',
                diagnostics={
                    'result_count': len(strategy_results),
                    'failure_count': len(failures),
                },
            )
        )

        return ExecutionReport(
            strategy_results=tuple(strategy_results),
            lifecycle=tuple(lifecycle),
            failures=tuple(failures),
        )
