import type { SymbolRecord } from './watchlists'

export interface SymbolSearchParams {
  search?: string
  asset_type?: 'stock' | 'etf'
  status?: 'active' | 'inactive'
  limit?: number
}

export interface SymbolSearchResponse {
  data: SymbolRecord[]
}
