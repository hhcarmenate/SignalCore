import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export type SignalDirection = 'bullish' | 'bearish'
export type ExecutionHint = 'call' | 'put'
export type RunStatus = 'Success' | 'Warning'
export type SignalStatus =
  | 'new'
  | 'pending_review'
  | 'accepted'
  | 'rejected'
  | 'expired'
  | 'actioned'
  | 'ignored'
export type ReviewPriority = 'high' | 'medium' | 'low'

export interface NavigationItem {
  label: string
  to: string
}

export interface PersistedSignalRecord {
  id: string
  symbol: string
  strategyKey: string
  strategyLabel: string
  direction: SignalDirection
  executionHint: ExecutionHint
  timeframe: string
  status: SignalStatus
  score: number
  confidence: number
  reviewPriority: ReviewPriority
  reviewScore: number
  signalGeneratedAt: string
  thesis: string
  entryPrice?: number
  stopLoss?: number
  targetPrice?: number
}

export interface CompactSignalItem {
  id: string
  symbol: string
  directionLabel: 'Bullish' | 'Bearish'
  hintLabel: 'Call' | 'Put'
  score: number
  bot: string
  timeframe: string
  statusLabel: string
}

export interface BotRun {
  name: string
  timeframe: string
  status: RunStatus
  scanned: number
  created: number
  duration: string
}

export interface KpiItem {
  label: string
  value: string
  footnote: string
  tone?: 'default' | 'warning'
}

const persistedSignalsSeed: PersistedSignalRecord[] = [
  {
    id: 'sig_nvda_4h_001',
    symbol: 'NVDA',
    strategyKey: 'trend_continuation',
    strategyLabel: 'Trend Continuation',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '4H',
    status: 'pending_review',
    score: 92,
    confidence: 89,
    reviewPriority: 'high',
    reviewScore: 95,
    signalGeneratedAt: '2026-03-30T21:52:00Z',
    thesis:
      'Strong continuation structure with trend alignment, momentum confirmation, and recent breakout hold above prior resistance.',
    entryPrice: 941.2,
    stopLoss: 923.4,
    targetPrice: 978.5,
  },
  {
    id: 'sig_meta_4h_002',
    symbol: 'META',
    strategyKey: 'breakout_confirmation',
    strategyLabel: 'Breakout Confirmation',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '4H',
    status: 'new',
    score: 87,
    confidence: 83,
    reviewPriority: 'high',
    reviewScore: 91,
    signalGeneratedAt: '2026-03-30T22:05:00Z',
    thesis:
      'Fresh breakout with volume support and higher low confirmation inside the active watchlist momentum basket.',
    entryPrice: 512.8,
    stopLoss: 503.6,
    targetPrice: 528.4,
  },
  {
    id: 'sig_tsla_4h_003',
    symbol: 'TSLA',
    strategyKey: 'mean_reversion_to_trend',
    strategyLabel: 'Mean Reversion to Trend',
    direction: 'bearish',
    executionHint: 'put',
    timeframe: '4H',
    status: 'pending_review',
    score: 84,
    confidence: 78,
    reviewPriority: 'medium',
    reviewScore: 82,
    signalGeneratedAt: '2026-03-30T20:44:00Z',
    thesis:
      'Price rejected the recovery zone and rolled back under short-term trend support with weakening breadth.',
    entryPrice: 171.6,
    stopLoss: 176.1,
    targetPrice: 162.9,
  },
  {
    id: 'sig_spy_1d_004',
    symbol: 'SPY',
    strategyKey: 'trend_continuation',
    strategyLabel: 'Trend Continuation',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '1D',
    status: 'accepted',
    score: 81,
    confidence: 76,
    reviewPriority: 'medium',
    reviewScore: 79,
    signalGeneratedAt: '2026-03-30T18:10:00Z',
    thesis:
      'Daily trend remains constructive with improving breadth and clean continuation structure above prior base.',
    entryPrice: 523.1,
    stopLoss: 516.8,
    targetPrice: 533.2,
  },
  {
    id: 'sig_qqq_1d_005',
    symbol: 'QQQ',
    strategyKey: 'breakout_confirmation',
    strategyLabel: 'Breakout Confirmation',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '1D',
    status: 'actioned',
    score: 79,
    confidence: 74,
    reviewPriority: 'low',
    reviewScore: 72,
    signalGeneratedAt: '2026-03-29T19:25:00Z',
    thesis:
      'Breakout follow-through remained valid and the setup already moved into execution workflow.',
    entryPrice: 447.8,
    stopLoss: 441.5,
    targetPrice: 456.7,
  },
  {
    id: 'sig_aapl_4h_006',
    symbol: 'AAPL',
    strategyKey: 'mean_reversion_to_trend',
    strategyLabel: 'Mean Reversion to Trend',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '4H',
    status: 'rejected',
    score: 68,
    confidence: 63,
    reviewPriority: 'low',
    reviewScore: 61,
    signalGeneratedAt: '2026-03-29T15:32:00Z',
    thesis:
      'Recovered from oversold conditions, but relative strength and confirmation quality were not strong enough.',
    entryPrice: 209.7,
    stopLoss: 206.2,
    targetPrice: 214.9,
  },
  {
    id: 'sig_msft_4h_007',
    symbol: 'MSFT',
    strategyKey: 'trend_continuation',
    strategyLabel: 'Trend Continuation',
    direction: 'bullish',
    executionHint: 'call',
    timeframe: '4H',
    status: 'ignored',
    score: 73,
    confidence: 69,
    reviewPriority: 'medium',
    reviewScore: 70,
    signalGeneratedAt: '2026-03-29T17:05:00Z',
    thesis:
      'Trend quality is fine, but opportunity was skipped in favor of stronger names in the same basket.',
    entryPrice: 428.4,
    stopLoss: 422.9,
    targetPrice: 436.8,
  },
  {
    id: 'sig_amzn_1d_008',
    symbol: 'AMZN',
    strategyKey: 'breakout_confirmation',
    strategyLabel: 'Breakout Confirmation',
    direction: 'bearish',
    executionHint: 'put',
    timeframe: '1D',
    status: 'expired',
    score: 71,
    confidence: 65,
    reviewPriority: 'low',
    reviewScore: 58,
    signalGeneratedAt: '2026-03-28T16:18:00Z',
    thesis:
      'Breakdown setup lost timing edge after the follow-through window closed without confirmation.',
    entryPrice: 178.6,
    stopLoss: 182.4,
    targetPrice: 171.5,
  },
]

export const useAppStore = defineStore('app', () => {
  const navigation = ref<NavigationItem[]>([
    { label: 'Dashboard', to: '/dashboard' },
    { label: 'Signals', to: '/signals' },
    { label: 'Watchlists', to: '/watchlists' },
    { label: 'Bots', to: '/bots' },
    { label: 'Notifications', to: '/notifications' },
    { label: 'Analytics', to: '/analytics' },
  ])

  const environment = ref({
    mode: 'Local / Dev',
    provider: 'Twelve Data',
    market: 'US Equities',
    marketStatus: 'Market Open',
    unreadCount: 3,
  })

  const persistedSignals = ref<PersistedSignalRecord[]>(persistedSignalsSeed)

  const topSignals = computed<CompactSignalItem[]>(() =>
    [...persistedSignals.value]
      .sort((left, right) => right.reviewScore - left.reviewScore)
      .slice(0, 3)
      .map((signal) => ({
        id: signal.id,
        symbol: signal.symbol,
        directionLabel: signal.direction === 'bullish' ? 'Bullish' : 'Bearish',
        hintLabel: signal.executionHint === 'call' ? 'Call' : 'Put',
        score: signal.score,
        bot: signal.strategyLabel,
        timeframe: signal.timeframe,
        statusLabel: signal.status.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()),
      })),
  )

  const botRuns = ref<BotRun[]>([
    {
      name: 'Trend Continuation',
      timeframe: '4H',
      status: 'Success',
      scanned: 17,
      created: 3,
      duration: '12.4s',
    },
    {
      name: 'Breakout Confirmation',
      timeframe: 'Daily',
      status: 'Success',
      scanned: 17,
      created: 2,
      duration: '9.1s',
    },
    {
      name: 'Mean Reversion',
      timeframe: '4H',
      status: 'Warning',
      scanned: 17,
      created: 1,
      duration: '14.8s',
    },
  ])

  const notifications = ref<string[]>([
    'New high-confidence signal detected for NVDA on 4H.',
    'Mean Reversion bot completed with one low-confidence candidate.',
    'Watchlist scan finished for Core Momentum list.',
  ])

  const watchlistSnapshot = ref([
    { label: 'Active watchlists', value: '3' },
    { label: 'Total symbols', value: '17' },
    { label: 'Last scan', value: '2 min ago' },
    { label: 'Top bot', value: 'Trend Continuation' },
  ])

  const dashboardKpis = computed<KpiItem[]>(() => [
    { label: 'Signals Today', value: '14', footnote: '+4 vs yesterday' },
    { label: 'High Confidence', value: '5', footnote: 'score ≥ 85' },
    { label: 'Active Bots', value: '3 / 3', footnote: 'all running' },
    { label: 'Issues', value: '1', footnote: 'needs review', tone: 'warning' },
  ])

  return {
    navigation,
    environment,
    persistedSignals,
    topSignals,
    botRuns,
    notifications,
    watchlistSnapshot,
    dashboardKpis,
  }
})
