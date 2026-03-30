from dataclasses import dataclass, field

from signalcore_scanner.contracts.strategy_execution_input import StrategyExecutionInput
from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput


@dataclass(frozen=True)
class ScannerStrategyResult:
    strategy_key: str
    symbol: str
    produced_signal: bool
    signals: tuple[ScannerSignalOutput, ...] = ()
    diagnostics: dict[str, float | int | str | bool] = field(default_factory=dict)


@dataclass(frozen=True)
class ScannerRunOutput:
    watchlist_id: int
    timeframe: str
    strategy_results: tuple[ScannerStrategyResult, ...]

    def signals(self) -> tuple[ScannerSignalOutput, ...]:
        return tuple(
            signal
            for result in self.strategy_results
            for signal in result.signals
        )


def build_strategy_result(
    execution_input: StrategyExecutionInput,
    signals: tuple[ScannerSignalOutput, ...],
    diagnostics: dict[str, float | int | str | bool] | None = None,
) -> ScannerStrategyResult:
    return ScannerStrategyResult(
        strategy_key=execution_input.strategy_key,
        symbol=execution_input.symbol.symbol,
        produced_signal=bool(signals),
        signals=signals,
        diagnostics=diagnostics or {},
    )
