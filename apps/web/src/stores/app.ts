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

export interface SignalScoreBreakdownItem {
  label: string
  value: number
  tone?: 'success' | 'warning' | 'danger' | 'neutral'
}

export interface SignalContextMetric {
  label: string
  value: string
}

export interface SignalReviewNote {
  id: string
  author: string
  body: string
  createdAt: string
}

export interface SignalAuditEvent {
  id: string
  eventType: string
  actionLabel: string
  fromStatus?: SignalStatus
  toStatus: SignalStatus
  reason: string
  occurredAt: string
}

export interface SignalActionItem {
  key: 'queue_review' | 'accept' | 'reject' | 'ignore' | 'mark_actioned' | 'expire'
  label: string
  tone: 'primary' | 'success' | 'danger' | 'neutral'
  enabled: boolean
  hint: string
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
  statusReason?: string
  score: number
  confidence: number
  rankingScore?: number
  rankingPosition?: number
  reviewPriority: ReviewPriority
  reviewScore: number
  signalGeneratedAt: string
  thesis: string
  entryPrice?: number
  stopLoss?: number
  targetPrice?: number
  expiresAt?: string
  reviewedAt?: string
  actionedAt?: string
  invalidatedAt?: string
  queuedForReviewAt?: string
  reviewSummary?: string
  reviewDecisionContext?: string
  reviewNotes: SignalReviewNote[]
  scoreBreakdown: SignalScoreBreakdownItem[]
  indicatorSnapshot: SignalContextMetric[]
  marketContext: SignalContextMetric[]
  sourceRunReference?: string
  sourceSignalReference?: string
  watchlistLabel?: string
  actions: SignalActionItem[]
  auditTrail: SignalAuditEvent[]
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

export type ScannerRunStatus = 'completed' | 'completed_with_errors' | 'failed'

export interface ScannerRunEvent {
  id: string
  label: string
  detail: string
  occurredAt: string
  tone?: 'success' | 'warning' | 'danger' | 'neutral'
}

export interface ScannerRunRecord {
  id: string
  runReference: string
  watchlist: string
  timeframe: string
  status: ScannerRunStatus
  symbolsScannedCount: number
  strategiesExecutedCount: number
  signalsFoundCount: number
  errorCount: number
  startedAt: string
  completedAt: string
  duration: string
  triggerType: string
  errorSummary?: string
  lifecycleEvents: ScannerRunEvent[]
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
    statusReason: 'Waiting for operator validation after breakout-hold confirmation.',
    score: 92,
    confidence: 89,
    rankingScore: 96,
    rankingPosition: 1,
    reviewPriority: 'high',
    reviewScore: 95,
    signalGeneratedAt: '2026-03-30T21:52:00Z',
    thesis:
      'Strong continuation structure with trend alignment, momentum confirmation, and recent breakout hold above prior resistance.',
    entryPrice: 941.2,
    stopLoss: 923.4,
    targetPrice: 978.5,
    expiresAt: '2026-04-01T14:00:00Z',
    queuedForReviewAt: '2026-03-30T21:54:00Z',
    reviewSummary: 'Setup is clean and actionable if strength persists above the breakout shelf.',
    reviewDecisionContext: 'Prioritize because it leads the momentum basket and has the strongest score breakdown today.',
    reviewNotes: [
      {
        id: 'note_nvda_1',
        author: 'System',
        body: 'Queued automatically after score cleared the high-priority threshold.',
        createdAt: '2026-03-30T21:54:00Z',
      },
      {
        id: 'note_nvda_2',
        author: 'Operator',
        body: 'Needs one last check against the next cash-session open before acceptance.',
        createdAt: '2026-03-30T22:18:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Trend alignment', value: 28, tone: 'success' },
      { label: 'Breakout structure', value: 24, tone: 'success' },
      { label: 'Relative strength', value: 21, tone: 'success' },
      { label: 'Volume support', value: 19, tone: 'warning' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '63.4' },
      { label: 'EMA 20 slope', value: 'Rising' },
      { label: 'ATR regime', value: 'Expanding' },
      { label: 'Volume vs avg', value: '1.28x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Bullish' },
      { label: 'Market regime', value: 'Risk-on continuation' },
      { label: 'Watchlist', value: 'Core Momentum' },
      { label: 'Direction context', value: 'Leader above prior resistance' },
    ],
    sourceRunReference: 'run_2026_03_30_4h_tc_01',
    sourceSignalReference: 'scan_nvda_4h_8451',
    watchlistLabel: 'Core Momentum',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already queued.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: true, hint: 'Ready for analyst acceptance.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: true, hint: 'Use if the breakout fails validation.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: true, hint: 'Hide without actioning the setup.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: true, hint: 'Use once execution is taken.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: true, hint: 'End the setup if timing is gone.' },
    ],
    auditTrail: [
      {
        id: 'audit_nvda_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Scanner persisted the setup after threshold pass.',
        occurredAt: '2026-03-30T21:52:00Z',
      },
      {
        id: 'audit_nvda_2',
        eventType: 'queue_review',
        actionLabel: 'Queued for review',
        fromStatus: 'new',
        toStatus: 'pending_review',
        reason: 'Review priority evaluated as high.',
        occurredAt: '2026-03-30T21:54:00Z',
      },
    ],
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
    statusReason: 'Fresh signal awaiting queue decision.',
    score: 87,
    confidence: 83,
    rankingScore: 89,
    rankingPosition: 2,
    reviewPriority: 'high',
    reviewScore: 91,
    signalGeneratedAt: '2026-03-30T22:05:00Z',
    thesis:
      'Fresh breakout with volume support and higher low confirmation inside the active watchlist momentum basket.',
    entryPrice: 512.8,
    stopLoss: 503.6,
    targetPrice: 528.4,
    expiresAt: '2026-04-01T15:30:00Z',
    reviewSummary: 'Strong candidate, but less mature than NVDA and still needs queueing.',
    reviewDecisionContext: 'Good upside if the breakout survives a retest; lower certainty than the lead setup.',
    reviewNotes: [
      {
        id: 'note_meta_1',
        author: 'System',
        body: 'Signal generated from breakout confirmation strategy with fresh persistence write.',
        createdAt: '2026-03-30T22:05:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Breakout quality', value: 26, tone: 'success' },
      { label: 'Volume confirmation', value: 22, tone: 'success' },
      { label: 'Trend context', value: 20, tone: 'warning' },
      { label: 'Timing quality', value: 19, tone: 'warning' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '61.1' },
      { label: 'EMA stack', value: 'Bullish' },
      { label: 'VWAP relation', value: 'Above VWAP' },
      { label: 'Volume vs avg', value: '1.42x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Bullish' },
      { label: 'Structure', value: 'Breakout above consolidation' },
      { label: 'Watchlist', value: 'Core Momentum' },
      { label: 'Confirmation', value: 'Higher low intact' },
    ],
    sourceRunReference: 'run_2026_03_30_4h_bc_01',
    sourceSignalReference: 'scan_meta_4h_1260',
    watchlistLabel: 'Core Momentum',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'primary', enabled: true, hint: 'Promote into the active review queue.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Queue it first.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: true, hint: 'Reject if breakout quality fades.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: true, hint: 'Skip without escalating it.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: false, hint: 'Requires review first.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: true, hint: 'Use once the timing window closes.' },
    ],
    auditTrail: [
      {
        id: 'audit_meta_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Breakout confirmation persisted from scanner output.',
        occurredAt: '2026-03-30T22:05:00Z',
      },
    ],
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
    statusReason: 'Counter-rally failed, pending validation against broader weakness.',
    score: 84,
    confidence: 78,
    rankingScore: 82,
    rankingPosition: 3,
    reviewPriority: 'medium',
    reviewScore: 82,
    signalGeneratedAt: '2026-03-30T20:44:00Z',
    thesis:
      'Price rejected the recovery zone and rolled back under short-term trend support with weakening breadth.',
    entryPrice: 171.6,
    stopLoss: 176.1,
    targetPrice: 162.9,
    expiresAt: '2026-03-31T20:00:00Z',
    queuedForReviewAt: '2026-03-30T20:48:00Z',
    reviewSummary: 'Worth reviewing, but market leadership is weaker and volatility is less clean.',
    reviewDecisionContext: 'Better as a secondary bearish candidate than as the lead short setup.',
    reviewNotes: [
      {
        id: 'note_tsla_1',
        author: 'Operator',
        body: 'Check if index weakness confirms the short thesis before acceptance.',
        createdAt: '2026-03-30T21:03:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Trend reclaim failure', value: 25, tone: 'success' },
      { label: 'Breadth weakness', value: 20, tone: 'warning' },
      { label: 'Volatility quality', value: 18, tone: 'warning' },
      { label: 'Timing precision', value: 21, tone: 'success' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '44.8' },
      { label: 'EMA 20 relation', value: 'Rejected below' },
      { label: 'ATR regime', value: 'Elevated' },
      { label: 'Volume vs avg', value: '1.11x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Mixed bearish' },
      { label: 'Structure', value: 'Recovery failure' },
      { label: 'Watchlist', value: 'High Beta Shorts' },
      { label: 'Direction context', value: 'Relative weakness vs QQQ' },
    ],
    sourceRunReference: 'run_2026_03_30_4h_mr_01',
    sourceSignalReference: 'scan_tsla_4h_4540',
    watchlistLabel: 'High Beta Shorts',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already in review.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: true, hint: 'Promote if broad market weakness confirms.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: true, hint: 'Reject if buyers reclaim the failure zone.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: true, hint: 'Stand down without invalidating.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: true, hint: 'Use once execution is taken.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: true, hint: 'Expire when timing is lost.' },
    ],
    auditTrail: [
      {
        id: 'audit_tsla_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Mean reversion strategy emitted a valid bearish setup.',
        occurredAt: '2026-03-30T20:44:00Z',
      },
      {
        id: 'audit_tsla_2',
        eventType: 'queue_review',
        actionLabel: 'Queued for review',
        fromStatus: 'new',
        toStatus: 'pending_review',
        reason: 'Score crossed the manual review threshold.',
        occurredAt: '2026-03-30T20:48:00Z',
      },
    ],
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
    statusReason: 'Approved for action monitoring.',
    score: 81,
    confidence: 76,
    rankingScore: 78,
    rankingPosition: 4,
    reviewPriority: 'medium',
    reviewScore: 79,
    signalGeneratedAt: '2026-03-30T18:10:00Z',
    thesis:
      'Daily trend remains constructive with improving breadth and clean continuation structure above prior base.',
    entryPrice: 523.1,
    stopLoss: 516.8,
    targetPrice: 533.2,
    expiresAt: '2026-04-02T20:00:00Z',
    reviewedAt: '2026-03-30T18:42:00Z',
    queuedForReviewAt: '2026-03-30T18:14:00Z',
    reviewSummary: 'Accepted as a quality index continuation reference signal.',
    reviewDecisionContext: 'Useful benchmark setup for broader market direction and relative strength checks.',
    reviewNotes: [
      {
        id: 'note_spy_1',
        author: 'Operator',
        body: 'Accepted mainly as a regime anchor rather than the highest-R multiple trade.',
        createdAt: '2026-03-30T18:42:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Trend alignment', value: 24, tone: 'success' },
      { label: 'Breadth support', value: 19, tone: 'warning' },
      { label: 'Structure quality', value: 20, tone: 'success' },
      { label: 'Timing quality', value: 18, tone: 'warning' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '58.3' },
      { label: 'EMA stack', value: 'Bullish' },
      { label: 'Breadth', value: 'Improving' },
      { label: 'Volume vs avg', value: '1.04x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Bullish' },
      { label: 'Structure', value: 'Continuation above prior base' },
      { label: 'Watchlist', value: 'Index Leaders' },
      { label: 'Direction context', value: 'Reference market trend' },
    ],
    sourceRunReference: 'run_2026_03_30_1d_tc_01',
    sourceSignalReference: 'scan_spy_1d_3021',
    watchlistLabel: 'Index Leaders',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already reviewed.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Already accepted.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: false, hint: 'Use only if status changes back to reviewable.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: true, hint: 'Can still be hidden from active queue.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: true, hint: 'Mark once execution is live.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: true, hint: 'Expire if thesis decays.' },
    ],
    auditTrail: [
      {
        id: 'audit_spy_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Trend continuation signal persisted.',
        occurredAt: '2026-03-30T18:10:00Z',
      },
      {
        id: 'audit_spy_2',
        eventType: 'queue_review',
        actionLabel: 'Queued for review',
        fromStatus: 'new',
        toStatus: 'pending_review',
        reason: 'Medium priority candidate entered review flow.',
        occurredAt: '2026-03-30T18:14:00Z',
      },
      {
        id: 'audit_spy_3',
        eventType: 'accept',
        actionLabel: 'Accepted',
        fromStatus: 'pending_review',
        toStatus: 'accepted',
        reason: 'Operator approved as regime-aligned continuation setup.',
        occurredAt: '2026-03-30T18:42:00Z',
      },
    ],
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
    statusReason: 'Execution already taken.',
    score: 79,
    confidence: 74,
    rankingScore: 75,
    rankingPosition: 5,
    reviewPriority: 'low',
    reviewScore: 72,
    signalGeneratedAt: '2026-03-29T19:25:00Z',
    thesis:
      'Breakout follow-through remained valid and the setup already moved into execution workflow.',
    entryPrice: 447.8,
    stopLoss: 441.5,
    targetPrice: 456.7,
    actionedAt: '2026-03-29T20:12:00Z',
    reviewedAt: '2026-03-29T19:54:00Z',
    queuedForReviewAt: '2026-03-29T19:31:00Z',
    reviewSummary: 'Actioned after confirmation held through the close.',
    reviewDecisionContext: 'Moderate conviction but clean enough to execute with reduced sizing.',
    reviewNotes: [
      {
        id: 'note_qqq_1',
        author: 'Operator',
        body: 'Marked actioned after post-breakout hold stayed intact into the next interval.',
        createdAt: '2026-03-29T20:12:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Breakout follow-through', value: 23, tone: 'success' },
      { label: 'Trend support', value: 19, tone: 'warning' },
      { label: 'Breadth confirmation', value: 17, tone: 'warning' },
      { label: 'Execution timing', value: 16, tone: 'success' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '57.5' },
      { label: 'EMA stack', value: 'Bullish' },
      { label: 'Relative strength', value: 'Stable' },
      { label: 'Volume vs avg', value: '1.08x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Bullish' },
      { label: 'Structure', value: 'Confirmed breakout' },
      { label: 'Watchlist', value: 'Index Leaders' },
      { label: 'Direction context', value: 'Execution already taken' },
    ],
    sourceRunReference: 'run_2026_03_29_1d_bc_01',
    sourceSignalReference: 'scan_qqq_1d_2210',
    watchlistLabel: 'Index Leaders',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already actioned.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Already actioned.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: false, hint: 'Not valid after actioning.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: false, hint: 'Use status controls instead.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: false, hint: 'Already actioned.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: true, hint: 'Expire if tracking window ends.' },
    ],
    auditTrail: [
      {
        id: 'audit_qqq_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Breakout confirmation persisted.',
        occurredAt: '2026-03-29T19:25:00Z',
      },
      {
        id: 'audit_qqq_2',
        eventType: 'accept',
        actionLabel: 'Accepted',
        fromStatus: 'pending_review',
        toStatus: 'accepted',
        reason: 'Operator validated the follow-through.',
        occurredAt: '2026-03-29T19:54:00Z',
      },
      {
        id: 'audit_qqq_3',
        eventType: 'mark_actioned',
        actionLabel: 'Marked actioned',
        fromStatus: 'accepted',
        toStatus: 'actioned',
        reason: 'Execution placed against the persisted setup.',
        occurredAt: '2026-03-29T20:12:00Z',
      },
    ],
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
    statusReason: 'Relative strength and confirmation quality were too weak.',
    score: 68,
    confidence: 63,
    rankingScore: 60,
    rankingPosition: 7,
    reviewPriority: 'low',
    reviewScore: 61,
    signalGeneratedAt: '2026-03-29T15:32:00Z',
    thesis:
      'Recovered from oversold conditions, but relative strength and confirmation quality were not strong enough.',
    entryPrice: 209.7,
    stopLoss: 206.2,
    targetPrice: 214.9,
    reviewedAt: '2026-03-29T16:10:00Z',
    invalidatedAt: '2026-03-29T16:10:00Z',
    reviewSummary: 'Rejected due to low-quality confirmation.',
    reviewDecisionContext: 'No need to force mean reversion setups when stronger leaders are available.',
    reviewNotes: [
      {
        id: 'note_aapl_1',
        author: 'Operator',
        body: 'Rejected: bounce lacked real leadership and failed the quality threshold.',
        createdAt: '2026-03-29T16:10:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Oversold rebound', value: 20, tone: 'warning' },
      { label: 'Trend support', value: 14, tone: 'danger' },
      { label: 'Relative strength', value: 12, tone: 'danger' },
      { label: 'Timing quality', value: 22, tone: 'success' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '47.9' },
      { label: 'EMA 20 relation', value: 'Flat' },
      { label: 'ATR regime', value: 'Normal' },
      { label: 'Volume vs avg', value: '0.94x' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Neutral' },
      { label: 'Structure', value: 'Weak rebound' },
      { label: 'Watchlist', value: 'Core Momentum' },
      { label: 'Direction context', value: 'Lagging peers' },
    ],
    sourceRunReference: 'run_2026_03_29_4h_mr_02',
    sourceSignalReference: 'scan_aapl_4h_7781',
    watchlistLabel: 'Core Momentum',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already resolved.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Rejected signals are not actionable.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: false, hint: 'Already rejected.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: true, hint: 'Can still be hidden from summaries.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: false, hint: 'Rejected signals cannot be actioned.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: false, hint: 'Already resolved through rejection.' },
    ],
    auditTrail: [
      {
        id: 'audit_aapl_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Mean reversion candidate persisted.',
        occurredAt: '2026-03-29T15:32:00Z',
      },
      {
        id: 'audit_aapl_2',
        eventType: 'reject',
        actionLabel: 'Rejected',
        fromStatus: 'pending_review',
        toStatus: 'rejected',
        reason: 'Confirmation quality was insufficient.',
        occurredAt: '2026-03-29T16:10:00Z',
      },
    ],
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
    statusReason: 'Skipped in favor of stronger peers.',
    score: 73,
    confidence: 69,
    rankingScore: 67,
    rankingPosition: 6,
    reviewPriority: 'medium',
    reviewScore: 70,
    signalGeneratedAt: '2026-03-29T17:05:00Z',
    thesis:
      'Trend quality is fine, but opportunity was skipped in favor of stronger names in the same basket.',
    entryPrice: 428.4,
    stopLoss: 422.9,
    targetPrice: 436.8,
    reviewedAt: '2026-03-29T17:26:00Z',
    reviewSummary: 'Ignored due to internal competition for capital.',
    reviewDecisionContext: 'Not a bad setup; just not better than the top-tier alternatives.',
    reviewNotes: [
      {
        id: 'note_msft_1',
        author: 'Operator',
        body: 'Ignored because NVDA and META offered cleaner momentum alignment.',
        createdAt: '2026-03-29T17:26:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Trend alignment', value: 21, tone: 'success' },
      { label: 'Relative strength', value: 15, tone: 'warning' },
      { label: 'Timing quality', value: 16, tone: 'warning' },
      { label: 'Leadership quality', value: 15, tone: 'danger' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '55.7' },
      { label: 'EMA stack', value: 'Bullish' },
      { label: 'Volume vs avg', value: '0.99x' },
      { label: 'ATR regime', value: 'Contained' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Bullish' },
      { label: 'Structure', value: 'Healthy continuation' },
      { label: 'Watchlist', value: 'Core Momentum' },
      { label: 'Direction context', value: 'Lower relative urgency' },
    ],
    sourceRunReference: 'run_2026_03_29_4h_tc_03',
    sourceSignalReference: 'scan_msft_4h_5520',
    watchlistLabel: 'Core Momentum',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Already handled.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Ignored signals are not active.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: false, hint: 'Ignored signals are already resolved.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: false, hint: 'Already ignored.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: false, hint: 'Ignored signals cannot be actioned.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: false, hint: 'Ignored signals do not need expiry.' },
    ],
    auditTrail: [
      {
        id: 'audit_msft_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Trend continuation candidate persisted.',
        occurredAt: '2026-03-29T17:05:00Z',
      },
      {
        id: 'audit_msft_2',
        eventType: 'ignore',
        actionLabel: 'Ignored',
        fromStatus: 'pending_review',
        toStatus: 'ignored',
        reason: 'Lower priority than competing setups.',
        occurredAt: '2026-03-29T17:26:00Z',
      },
    ],
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
    statusReason: 'The timing window closed without confirmation.',
    score: 71,
    confidence: 65,
    rankingScore: 59,
    rankingPosition: 8,
    reviewPriority: 'low',
    reviewScore: 58,
    signalGeneratedAt: '2026-03-28T16:18:00Z',
    thesis:
      'Breakdown setup lost timing edge after the follow-through window closed without confirmation.',
    entryPrice: 178.6,
    stopLoss: 182.4,
    targetPrice: 171.5,
    expiresAt: '2026-03-29T19:00:00Z',
    invalidatedAt: '2026-03-29T19:00:00Z',
    reviewSummary: 'Expired naturally after no follow-through.',
    reviewDecisionContext: 'Useful example of a setup that aged out without enough pressure.',
    reviewNotes: [
      {
        id: 'note_amzn_1',
        author: 'System',
        body: 'Signal expired when the confirmation window closed.',
        createdAt: '2026-03-29T19:00:00Z',
      },
    ],
    scoreBreakdown: [
      { label: 'Breakdown structure', value: 19, tone: 'warning' },
      { label: 'Trend pressure', value: 17, tone: 'warning' },
      { label: 'Follow-through quality', value: 10, tone: 'danger' },
      { label: 'Timing quality', value: 13, tone: 'danger' },
    ],
    indicatorSnapshot: [
      { label: 'RSI 14', value: '48.5' },
      { label: 'EMA stack', value: 'Mixed' },
      { label: 'Volume vs avg', value: '0.91x' },
      { label: 'ATR regime', value: 'Normal' },
    ],
    marketContext: [
      { label: 'Higher timeframe bias', value: 'Neutral bearish' },
      { label: 'Structure', value: 'Failed breakdown follow-through' },
      { label: 'Watchlist', value: 'Consumer Megacaps' },
      { label: 'Direction context', value: 'Thesis aged out' },
    ],
    sourceRunReference: 'run_2026_03_28_1d_bc_01',
    sourceSignalReference: 'scan_amzn_1d_1884',
    watchlistLabel: 'Consumer Megacaps',
    actions: [
      { key: 'queue_review', label: 'Queue for review', tone: 'neutral', enabled: false, hint: 'Expired signals are not reviewable.' },
      { key: 'accept', label: 'Accept', tone: 'success', enabled: false, hint: 'Expired signals are not actionable.' },
      { key: 'reject', label: 'Reject', tone: 'danger', enabled: false, hint: 'Expired signals are already resolved.' },
      { key: 'ignore', label: 'Ignore', tone: 'neutral', enabled: false, hint: 'No longer needed.' },
      { key: 'mark_actioned', label: 'Mark actioned', tone: 'primary', enabled: false, hint: 'Expired signals cannot be actioned.' },
      { key: 'expire', label: 'Expire', tone: 'danger', enabled: false, hint: 'Already expired.' },
    ],
    auditTrail: [
      {
        id: 'audit_amzn_1',
        eventType: 'signal_created',
        actionLabel: 'Signal created',
        toStatus: 'new',
        reason: 'Breakdown confirmation candidate persisted.',
        occurredAt: '2026-03-28T16:18:00Z',
      },
      {
        id: 'audit_amzn_2',
        eventType: 'expire',
        actionLabel: 'Expired',
        fromStatus: 'pending_review',
        toStatus: 'expired',
        reason: 'Timing window closed without valid continuation.',
        occurredAt: '2026-03-29T19:00:00Z',
      },
    ],
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

  const scannerRuns = ref<ScannerRunRecord[]>([
    {
      id: 'scan_run_001',
      runReference: 'run_2026_03_31_4h_tc_01',
      watchlist: 'Core Momentum',
      timeframe: '4H',
      status: 'completed',
      symbolsScannedCount: 17,
      strategiesExecutedCount: 3,
      signalsFoundCount: 4,
      errorCount: 0,
      startedAt: '2026-03-31T11:30:00Z',
      completedAt: '2026-03-31T11:30:12Z',
      duration: '12.4s',
      triggerType: 'scheduler',
      errorSummary: 'No tracked execution issues.',
      lifecycleEvents: [
        { id: 'run1_evt1', label: 'Run queued', detail: 'Scheduler triggered the 4H scan window.', occurredAt: '2026-03-31T11:29:58Z', tone: 'neutral' },
        { id: 'run1_evt2', label: 'Execution completed', detail: 'All strategy targets executed successfully.', occurredAt: '2026-03-31T11:30:12Z', tone: 'success' },
      ],
    },
    {
      id: 'scan_run_002',
      runReference: 'run_2026_03_31_1d_bc_01',
      watchlist: 'Index Leaders',
      timeframe: '1D',
      status: 'completed_with_errors',
      symbolsScannedCount: 12,
      strategiesExecutedCount: 2,
      signalsFoundCount: 2,
      errorCount: 1,
      startedAt: '2026-03-31T10:00:00Z',
      completedAt: '2026-03-31T10:00:09Z',
      duration: '9.1s',
      triggerType: 'scheduler',
      errorSummary: 'One symbol failed normalization during persistence handoff.',
      lifecycleEvents: [
        { id: 'run2_evt1', label: 'Run queued', detail: 'Daily breakout scan started from the scheduler.', occurredAt: '2026-03-31T09:59:58Z', tone: 'neutral' },
        { id: 'run2_evt2', label: 'Partial error', detail: 'One symbol response failed validation and was skipped.', occurredAt: '2026-03-31T10:00:06Z', tone: 'warning' },
        { id: 'run2_evt3', label: 'Run completed', detail: 'Scanner finished with one tracked warning.', occurredAt: '2026-03-31T10:00:09Z', tone: 'warning' },
      ],
    },
    {
      id: 'scan_run_003',
      runReference: 'run_2026_03_31_4h_mr_01',
      watchlist: 'High Beta Shorts',
      timeframe: '4H',
      status: 'failed',
      symbolsScannedCount: 17,
      strategiesExecutedCount: 1,
      signalsFoundCount: 0,
      errorCount: 3,
      startedAt: '2026-03-31T08:15:00Z',
      completedAt: '2026-03-31T08:15:14Z',
      duration: '14.8s',
      triggerType: 'manual rerun',
      errorSummary: 'Provider retry budget exhausted and run terminated before completion.',
      lifecycleEvents: [
        { id: 'run3_evt1', label: 'Manual rerun', detail: 'Operator retriggered the failed bearish scan window.', occurredAt: '2026-03-31T08:14:58Z', tone: 'neutral' },
        { id: 'run3_evt2', label: 'Provider failure', detail: 'Repeated upstream fetch failures hit the retry ceiling.', occurredAt: '2026-03-31T08:15:10Z', tone: 'danger' },
        { id: 'run3_evt3', label: 'Run failed', detail: 'Run stopped before all target symbols could finish.', occurredAt: '2026-03-31T08:15:14Z', tone: 'danger' },
      ],
    },
    {
      id: 'scan_run_004',
      runReference: 'run_2026_03_30_4h_tc_04',
      watchlist: 'Core Momentum',
      timeframe: '4H',
      status: 'completed',
      symbolsScannedCount: 17,
      strategiesExecutedCount: 3,
      signalsFoundCount: 3,
      errorCount: 0,
      startedAt: '2026-03-30T19:30:00Z',
      completedAt: '2026-03-30T19:30:11Z',
      duration: '11.1s',
      triggerType: 'scheduler',
      errorSummary: 'Healthy run with no anomalies.',
      lifecycleEvents: [
        { id: 'run4_evt1', label: 'Run queued', detail: 'Scheduled 4H trend scan started on time.', occurredAt: '2026-03-30T19:29:58Z', tone: 'neutral' },
        { id: 'run4_evt2', label: 'Run completed', detail: 'Signal persistence completed without warnings.', occurredAt: '2026-03-30T19:30:11Z', tone: 'success' },
      ],
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

  const findSignalById = (signalId: string) => persistedSignals.value.find((signal) => signal.id === signalId) ?? null
  const findScannerRunByReference = (runReference: string) =>
    scannerRuns.value.find((run) => run.runReference === runReference) ?? null

  return {
    navigation,
    environment,
    persistedSignals,
    topSignals,
    botRuns,
    scannerRuns,
    notifications,
    watchlistSnapshot,
    dashboardKpis,
    findSignalById,
    findScannerRunByReference,
  }
})
