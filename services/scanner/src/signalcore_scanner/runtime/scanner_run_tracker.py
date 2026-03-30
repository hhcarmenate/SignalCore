from signalcore_scanner.runtime.execution_report import ExecutionReport
from signalcore_scanner.runtime.scanner_run_error import ScannerRunError
from signalcore_scanner.runtime.scanner_run_record import ScannerRunRecord
from signalcore_scanner.runtime.scanner_run_summary import ScannerRunSummary


class ScannerRunTracker:
    def build_record(self, execution_report: ExecutionReport, started_at: str, completed_at: str) -> ScannerRunRecord:
        watchlist_id = execution_report.lifecycle[0].watchlist_id
        timeframe = execution_report.lifecycle[0].timeframe
        errors = tuple(
            ScannerRunError(
                strategy_key=failure.strategy_key,
                symbol=failure.symbol,
                message=failure.message,
                diagnostics=failure.diagnostics,
            )
            for failure in execution_report.failures
        )

        symbols_scanned_count = len({result.symbol for result in execution_report.strategy_results})
        strategies_executed_count = len(execution_report.strategy_results)
        signals_found_count = len([
            signal
            for result in execution_report.strategy_results
            for signal in result.signals
        ])
        error_count = len(errors)

        if execution_report.failures and execution_report.strategy_results:
            status = 'completed_with_errors'
        elif execution_report.failures:
            status = 'failed'
        else:
            status = 'completed'

        return ScannerRunRecord(
            watchlist_id=watchlist_id,
            timeframe=timeframe,
            status=status,
            started_at=started_at,
            completed_at=completed_at,
            symbols_scanned_count=symbols_scanned_count,
            strategies_executed_count=strategies_executed_count,
            signals_found_count=signals_found_count,
            error_count=error_count,
            metadata={
                'lifecycle_events': len(execution_report.lifecycle),
            },
            errors=errors,
        )

    def summarize(self, record: ScannerRunRecord) -> ScannerRunSummary:
        return ScannerRunSummary(
            status=record.status,
            symbols_scanned_count=record.symbols_scanned_count,
            strategies_executed_count=record.strategies_executed_count,
            signals_found_count=record.signals_found_count,
            error_count=record.error_count,
        )
