from dataclasses import dataclass


@dataclass(frozen=True)
class SqlCandleQuerySpec:
    sql: str
    bindings: dict[str, object]
