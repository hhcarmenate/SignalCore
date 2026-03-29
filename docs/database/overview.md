# Database Overview

This project stores watchlists, market symbols, bot execution history, generated signals, UI notifications, and later signal performance outcomes.

The system is designed to support:

- stock-based signal detection
- options-oriented execution hints (`call`, `put`)
- multiple bot strategies
- future multi-domain expansion:
  - crypto
  - prediction markets
  - sports betting

## Initial database engine
- PostgreSQL

## Schema ownership
The official schema is defined through:

- Laravel migrations

Python bots must write to the schema defined by Laravel, not create or mutate database structure independently.

## Initial scope
Version 1 focuses on:

- single-user usage
- no authentication
- stocks / ETFs as the signal source
- options as execution hints
- UI-only notifications
