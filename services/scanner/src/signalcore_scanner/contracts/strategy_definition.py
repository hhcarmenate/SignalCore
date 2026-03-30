from dataclasses import dataclass, field


@dataclass(frozen=True)
class StrategyDefinition:
    key: str
    name: str
    description: str
    priority: int = 100
    directional_biases: tuple[str, ...] = ('bullish', 'bearish')
    execution_hints: tuple[str, ...] = ('call', 'put')
    enabled_by_default: bool = True
    included_in_mvp: bool = True
    notes: tuple[str, ...] = field(default_factory=tuple)
