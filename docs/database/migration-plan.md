# Migration Plan

## Recommended migration order

1. create `symbols`
2. create `watchlists`
3. create `watchlist_items`
4. create `bots`
5. create `bot_runs`
6. create `signals`
7. create `notifications`
8. create `signal_outcomes`

## Seeder order

1. seed symbols
2. seed default watchlist(s)
3. seed watchlist items
4. seed bots

## Implementation notes
- create schema only via Laravel migrations
- use seeders for initial fixed data
- Python bots must assume schema already exists
