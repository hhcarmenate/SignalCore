import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

export type SignalDirection = 'Bullish' | 'Bearish'
export type SignalHint = 'Call' | 'Put'
export type RunStatus = 'Success' | 'Warning'

export interface NavigationItem {
  label: string
  to: string
}

export interface SignalItem {
  symbol: string
  direction: SignalDirection
  hint: SignalHint
  score: number
  bot: string
  timeframe: string
  status: string
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

  const topSignals = ref<SignalItem[]>([
    {
      symbol: 'NVDA',
      direction: 'Bullish',
      hint: 'Call',
      score: 92,
      bot: 'Trend Continuation',
      timeframe: '4H',
      status: 'New',
    },
    {
      symbol: 'QQQ',
      direction: 'Bullish',
      hint: 'Call',
      score: 88,
      bot: 'Breakout Confirmation',
      timeframe: 'Daily',
      status: 'Reviewed',
    },
    {
      symbol: 'TSLA',
      direction: 'Bearish',
      hint: 'Put',
      score: 81,
      bot: 'Mean Reversion',
      timeframe: '4H',
      status: 'New',
    },
  ])

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
    topSignals,
    botRuns,
    notifications,
    watchlistSnapshot,
    dashboardKpis,
  }
})
