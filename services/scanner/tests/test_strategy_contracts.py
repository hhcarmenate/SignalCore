import unittest

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.scanner_run_output import ScannerRunOutput, build_strategy_result
from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput, TradeLevels
from signalcore_scanner.contracts.strategy_execution_input import MarketContextSnapshot, StrategyExecutionInput
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol


class StrategyContractsTest(unittest.TestCase):
    def test_signal_output_payload_is_stable_and_structured(self) -> None:
        signal = ScannerSignalOutput(
            strategy_key='trend_continuation',
            symbol='NVDA',
            timeframe='4h',
            direction='bullish',
            thesis='Price reclaimed the trend continuation zone with supporting momentum.',
            confidence=0.82,
            score=87.5,
            signal_category='trend_continuation',
            execution_hint='call',
            levels=TradeLevels(entry=100.0, stop_loss=95.0, target=112.0),
            indicators={'ema_20': 98.2, 'ema_50': 94.6},
            context={'trend_bias': 'bullish'},
            metadata={'source': 'scanner'},
        )

        payload = signal.to_payload()

        self.assertEqual('trend_continuation', payload['strategy_key'])
        self.assertEqual('NVDA', payload['symbol'])
        self.assertEqual('4h', payload['timeframe'])
        self.assertEqual('bullish', payload['direction'])
        self.assertEqual(0.82, payload['confidence'])
        self.assertEqual(87.5, payload['score'])
        self.assertEqual(100.0, payload['levels']['entry'])
        self.assertEqual('bullish', payload['context']['trend_bias'])

    def test_strategy_result_builder_uses_normalized_execution_input(self) -> None:
        execution_input = StrategyExecutionInput(
            strategy_key='breakout_confirmation',
            watchlist_id=5,
            symbol=WatchlistSymbol(symbol_id=10, symbol='SPY', asset_type='etf', market='us_equities'),
            timeframe='1d',
            candles=(
                CandlePoint(10, 'SPY', '1d', '2026-03-30T00:00:00+00:00', 1, 2, 0.5, 1.5, 100, True),
            ),
            market_context=MarketContextSnapshot(trend_bias='bullish', regime='trend', volatility_state='normal'),
            max_lookback=200,
            run_metadata={'trigger': 'scheduled'},
        )

        signal = ScannerSignalOutput(
            strategy_key='breakout_confirmation',
            symbol='SPY',
            timeframe='1d',
            direction='bullish',
            thesis='Daily breakout confirmed above resistance.',
            confidence=0.74,
            score=79.0,
            signal_category='breakout_confirmation',
        )

        result = build_strategy_result(execution_input, (signal,), {'candles_considered': 1})

        self.assertEqual('breakout_confirmation', result.strategy_key)
        self.assertEqual('SPY', result.symbol)
        self.assertTrue(result.produced_signal)
        self.assertEqual(1, len(result.signals))
        self.assertEqual(1, result.diagnostics['candles_considered'])

    def test_run_output_flattens_strategy_signals(self) -> None:
        signal_a = ScannerSignalOutput(
            strategy_key='trend_continuation',
            symbol='QQQ',
            timeframe='4h',
            direction='bullish',
            thesis='Continuation trigger.',
            confidence=0.7,
            score=75.0,
            signal_category='trend_continuation',
        )
        signal_b = ScannerSignalOutput(
            strategy_key='mean_reversion_to_trend',
            symbol='QQQ',
            timeframe='4h',
            direction='bullish',
            thesis='Pullback reset completed.',
            confidence=0.69,
            score=73.0,
            signal_category='mean_reversion_to_trend',
        )

        run_output = ScannerRunOutput(
            watchlist_id=3,
            timeframe='4h',
            strategy_results=(
                build_strategy_result(
                    StrategyExecutionInput(
                        strategy_key='trend_continuation',
                        watchlist_id=3,
                        symbol=WatchlistSymbol(symbol_id=9, symbol='QQQ', asset_type='etf', market='us_equities'),
                        timeframe='4h',
                        candles=(),
                    ),
                    (signal_a,),
                ),
                build_strategy_result(
                    StrategyExecutionInput(
                        strategy_key='mean_reversion_to_trend',
                        watchlist_id=3,
                        symbol=WatchlistSymbol(symbol_id=9, symbol='QQQ', asset_type='etf', market='us_equities'),
                        timeframe='4h',
                        candles=(),
                    ),
                    (signal_b,),
                ),
            ),
        )

        self.assertEqual(2, len(run_output.signals()))
        self.assertEqual(['trend_continuation', 'mean_reversion_to_trend'], [signal.strategy_key for signal in run_output.signals()])


if __name__ == '__main__':
    unittest.main()
