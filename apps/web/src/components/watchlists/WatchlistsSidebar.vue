<script setup lang="ts">
import type { Watchlist } from '../../types/watchlists'

defineProps<{
  watchlists: Watchlist[]
  selectedId?: number
  isLoading?: boolean
}>()

const emit = defineEmits<{
  select: [id: number]
  delete: [id: number]
}>()
</script>

<template>
  <div>
    <div class="section-header compact">
      <div>
        <p class="eyebrow">Watchlists</p>
        <h3>Your lists</h3>
      </div>
      <span v-if="isLoading" class="badge badge-neutral">Loading…</span>
    </div>

    <div class="watchlist-list">
      <button
        v-for="watchlist in watchlists"
        :key="watchlist.id"
        type="button"
        class="watchlist-list-item"
        :class="{ active: selectedId === watchlist.id }"
        @click="emit('select', watchlist.id)"
      >
        <div class="watchlist-copy">
          <strong>{{ watchlist.name }}</strong>
          <p>{{ watchlist.description || 'No description yet.' }}</p>
          <small>{{ watchlist.items_count ?? 0 }} tracked items · {{ watchlist.market_type }}</small>
        </div>

        <span class="watchlist-actions-inline">
          <span class="badge" :class="watchlist.is_active ? 'badge-success' : 'badge-warning'">
            {{ watchlist.is_active ? 'Active' : 'Inactive' }}
          </span>
          <span class="danger-link" @click.stop="emit('delete', watchlist.id)">Delete</span>
        </span>
      </button>

      <div v-if="isLoading" class="empty-state-panel">
        <p class="empty-state-title">Loading watchlists</p>
        <p class="empty-state-copy">Pulling current lists and item counts from the API.</p>
      </div>

      <div v-else-if="watchlists.length === 0" class="empty-state-panel">
        <p class="empty-state-title">No watchlists yet</p>
        <p class="empty-state-copy">Create your first list and start organizing symbols for scans.</p>
      </div>
    </div>
  </div>
</template>
