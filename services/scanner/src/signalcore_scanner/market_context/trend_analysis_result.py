from dataclasses import dataclass

from signalcore_scanner.contracts.strategy_execution_input import MarketContextSnapshot


@dataclass(frozen=True)
class TrendAnalysisResult:
    context: MarketContextSnapshot
