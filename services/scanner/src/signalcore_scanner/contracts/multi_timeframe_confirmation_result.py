from dataclasses import dataclass, field


@dataclass(frozen=True)
class MultiTimeframeConfirmationResult:
    is_confirmed: bool
    alignment_score: float
    conflict_detected: bool
    passed_minimum_threshold: bool
    notes: tuple[str, ...] = field(default_factory=tuple)
