from dataclasses import dataclass
from typing import Protocol

from signalcore_scanner.contracts.strategy_state import StrategyState


class StrategyStateRepository(Protocol):
    def get_states(self) -> list[StrategyState]: ...


@dataclass
class InMemoryStrategyStateRepository:
    states: list[StrategyState]

    def get_states(self) -> list[StrategyState]:
        return self.states
