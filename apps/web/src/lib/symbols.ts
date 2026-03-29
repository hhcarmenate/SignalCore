import { api } from './api'
import type { SymbolSearchParams, SymbolSearchResponse } from '../types/symbols'

function buildQuery(params: SymbolSearchParams = {}): string {
  const query = new URLSearchParams()

  if (params.search) {
    query.set('search', params.search)
  }

  if (params.asset_type) {
    query.set('asset_type', params.asset_type)
  }

  if (params.status) {
    query.set('status', params.status)
  }

  if (params.limit) {
    query.set('limit', String(params.limit))
  }

  const serialized = query.toString()

  return serialized ? `?${serialized}` : ''
}

export function searchSymbols(params: SymbolSearchParams = {}) {
  return api.get<SymbolSearchResponse>(`/symbols${buildQuery(params)}`)
}
