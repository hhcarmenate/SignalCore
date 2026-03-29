export const MARKET_TYPE_OPTIONS = [
  { value: 'us_equities', label: 'US Equities', assetTypes: ['stock', 'etf', 'option'] },
  { value: 'crypto', label: 'Crypto', assetTypes: ['crypto'] },
  { value: 'sports', label: 'Sports', assetTypes: ['sports_bet'] },
  { value: 'prediction', label: 'Prediction', assetTypes: ['prediction_market'] },
] as const

export type MarketType = (typeof MARKET_TYPE_OPTIONS)[number]['value']

export function getAllowedAssetTypes(marketType: string): string[] {
  const found = MARKET_TYPE_OPTIONS.find((option) => option.value === marketType)

  return found ? [...found.assetTypes] : []
}

export function getMarketTypeLabel(marketType: string): string {
  return MARKET_TYPE_OPTIONS.find((option) => option.value === marketType)?.label ?? marketType
}
