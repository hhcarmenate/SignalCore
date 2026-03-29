export interface SymbolRecord {
  id: number
  asset_type: string
  symbol: string
  name: string | null
  market: string
  exchange: string | null
  status: string
  currency: string | null
  provider: string
  provider_symbol: string | null
}

export interface WatchlistItem {
  id: number
  watchlist_id: number
  symbol_id: number
  notes: string | null
  symbol: SymbolRecord
}

export interface Watchlist {
  id: number
  name: string
  description: string | null
  market_type: string
  is_active: boolean
  items_count?: number
  items?: WatchlistItem[]
}

export interface WatchlistListResponse {
  data: Watchlist[]
}

export interface WatchlistDetailResponse {
  data: Watchlist
}

export interface CreateWatchlistPayload {
  name: string
  description?: string
  market_type: string
  is_active?: boolean
}

export interface CreateWatchlistItemPayload {
  notes?: string
  symbol_id?: number
  symbol?: {
    asset_type: string
    symbol: string
    name?: string
    exchange?: string
    currency?: string
    provider?: string
    provider_symbol?: string
  }
}
