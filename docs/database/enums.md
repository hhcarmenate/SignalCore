# Suggested Enums

## symbols.asset_type
- `stock`
- `etf`

## watchlists.source
- `manual`
- `system`
- `strategy`

## bots.domain
- `stock`
- future:
  - `crypto`
  - `prediction_market`
  - `sports_betting`

## bot_runs.status
- `pending`
- `running`
- `success`
- `failed`

## signals.market_type
- `equities_options`
- future:
  - `crypto_spot`
  - `crypto_derivatives`
  - `event_contracts`
  - `sports_market`

## signals.timeframe
- `daily`
- `4h`
- future:
  - `1h`

## signals.direction
- `bullish`
- `bearish`
- future:
  - `long`
  - `short`
  - `yes`
  - `no`
  - `over`
  - `under`

## signals.execution_hint
- `call`
- `put`
- future:
  - `buy_spot`
  - `sell_spot`
  - `long_future`
  - `short_future`
  - `buy_yes`
  - `buy_no`
  - `moneyline`
  - `spread`
  - `total_over`
  - `total_under`

## signals.status
- `new`
- `read`
- `reviewed`
- `dismissed`
- `expired`

## notifications.channel
- `ui`
- future:
  - `sms`
  - `phone`
  - `email`
  - `push`
