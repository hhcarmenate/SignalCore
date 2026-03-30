from dataclasses import dataclass


@dataclass(frozen=True)
class StrategyDefinition:
    key: str
    name: str
    description: str
    enabled_by_default: bool = True
