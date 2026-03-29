<script setup lang="ts">
import type { Watchlist } from '../../types/watchlists'

defineProps<{
  watchlist: Watchlist | null
  isLoading?: boolean
}>()

const emit = defineEmits<{
  remove: [itemId: number]
}>()
</script>

<template>
  <div>
    <div v-if="watchlist">
      <div class="section-header compact">
        <div>
          <p class="eyebrow">Detail</p>
          <h3>{{ watchlist.name }}</h3>
        </div>

        <div class="detail-actions">
          <slot name="actions" />
          <span class="badge" :class="watchlist.is_active ? 'badge-success' : 'badge-warning'">
            {{ watchlist.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
      </div>

      <p v-if="watchlist.description" class="placeholder-copy">{{ watchlist.description }}</p>

      <div class="watchlist-summary-grid top-gap">
        <div class="summary-stat">
          <span>Tracked items</span>
          <strong>{{ watchlist.items?.length ?? 0 }}</strong>
        </div>
        <div class="summary-stat">
          <span>Asset coverage</span>
          <strong>{{ new Set((watchlist.items ?? []).map((item) => item.symbol.asset_type)).size }}</strong>
        </div>
        <div class="summary-stat">
          <span>Market type</span>
          <strong>{{ watchlist.market_type }}</strong>
        </div>
      </div>

      <div class="table-shell top-gap">
        <table>
          <thead>
            <tr>
              <th>Symbol</th>
              <th>Name</th>
              <th>Asset Type</th>
              <th>Market</th>
              <th>Notes</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in watchlist.items ?? []" :key="item.id">
              <td class="symbol-cell">{{ item.symbol.symbol }}</td>
              <td>{{ item.symbol.name ?? '—' }}</td>
              <td>{{ item.symbol.asset_type }}</td>
              <td>{{ item.symbol.market }}</td>
              <td>{{ item.notes || '—' }}</td>
              <td>
                <button class="ghost-button danger-button" type="button" @click="emit('remove', item.id)">
                  Remove
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="(watchlist.items ?? []).length === 0" class="empty-state-panel top-gap">
        <p class="empty-state-title">No symbols in this watchlist</p>
        <p class="empty-state-copy">Use the add symbol modal to put the first symbol into this list.</p>
      </div>
    </div>

    <div v-else-if="isLoading" class="empty-state-panel">
      <p class="empty-state-title">Loading watchlist details</p>
      <p class="empty-state-copy">Fetching selected watchlist items and symbol metadata.</p>
    </div>

    <div v-else class="empty-state-panel">
      <p class="empty-state-title">No watchlist selected</p>
      <p class="empty-state-copy">Pick a list from the left panel to inspect its symbols here.</p>
    </div>
  </div>
</template>
