from signalcore_scanner.contracts.candle_query import CandleQuery
from signalcore_scanner.data_access.sql_candle_query_spec import SqlCandleQuerySpec


class PostgresCandleQueryBuilder:
    def build(self, query: CandleQuery, symbol_ids: list[int]) -> SqlCandleQuerySpec:
        sql = """
            WITH ranked_candles AS (
                SELECT
                    c.symbol_id,
                    s.symbol,
                    c.timeframe,
                    c.bar_time,
                    c.open,
                    c.high,
                    c.low,
                    c.close,
                    c.volume,
                    c.is_final,
                    ROW_NUMBER() OVER (
                        PARTITION BY c.symbol_id, c.timeframe
                        ORDER BY c.bar_time DESC
                    ) AS row_number
                FROM candles c
                INNER JOIN symbols s ON s.id = c.symbol_id
                WHERE c.symbol_id = ANY(:symbol_ids)
                  AND c.timeframe = :timeframe
                  AND (:only_final = FALSE OR c.is_final = TRUE)
            )
            SELECT
                symbol_id,
                symbol,
                timeframe,
                bar_time,
                open,
                high,
                low,
                close,
                volume,
                is_final
            FROM ranked_candles
            WHERE row_number <= :lookback
            ORDER BY symbol_id ASC, bar_time ASC
        """.strip()

        return SqlCandleQuerySpec(
            sql=sql,
            bindings={
                'symbol_ids': symbol_ids,
                'timeframe': query.timeframe,
                'lookback': query.lookback,
                'only_final': query.only_final,
            },
        )
