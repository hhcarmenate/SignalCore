<script setup lang="ts">
import { useAppStore } from '../../stores/app'
import SectionHeader from '../ui/SectionHeader.vue'
import StatusBadge from '../ui/StatusBadge.vue'

const appStore = useAppStore()
</script>

<template>
  <div>
    <SectionHeader eyebrow="Priority Feed" title="Top signals" action-label="View all" />

    <div class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Symbol</th>
            <th>Direction</th>
            <th>Hint</th>
            <th>Score</th>
            <th>Bot</th>
            <th>Timeframe</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="signal in appStore.topSignals" :key="`${signal.symbol}-${signal.bot}`">
            <td class="symbol-cell">{{ signal.symbol }}</td>
            <td>
              <StatusBadge
                :label="signal.direction"
                :tone="signal.direction === 'Bullish' ? 'success' : 'danger'"
              />
            </td>
            <td>
              <StatusBadge :label="signal.hint" tone="neutral" />
            </td>
            <td>{{ signal.score }}</td>
            <td>{{ signal.bot }}</td>
            <td>{{ signal.timeframe }}</td>
            <td>
              <StatusBadge :label="signal.status" tone="outline" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
