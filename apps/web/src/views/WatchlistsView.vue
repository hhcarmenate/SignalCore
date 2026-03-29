<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import AddWatchlistItemForm from '../components/watchlists/AddWatchlistItemForm.vue'
import CreateWatchlistForm from '../components/watchlists/CreateWatchlistForm.vue'
import WatchlistDetail from '../components/watchlists/WatchlistDetail.vue'
import WatchlistsSidebar from '../components/watchlists/WatchlistsSidebar.vue'
import BaseModal from '../components/ui/BaseModal.vue'
import ConfirmModal from '../components/ui/ConfirmModal.vue'
import AppIcon from '../components/ui/AppIcon.vue'
import { useWatchlistsStore } from '../stores/watchlists'

const watchlistsStore = useWatchlistsStore()

const isCreateModalOpen = ref(false)
const isAddTickerModalOpen = ref(false)
const deleteWatchlistId = ref<number | null>(null)
const deleteTickerId = ref<number | null>(null)

const selectedItemsCount = computed(() => watchlistsStore.selectedWatchlist?.items?.length ?? 0)

onMounted(async () => {
  await watchlistsStore.fetchWatchlists()
})

async function handleCreateWatchlist(payload: {
  name: string
  description?: string
  market_type: string
  is_active: boolean
}) {
  await watchlistsStore.createWatchlist(payload)
  isCreateModalOpen.value = false
}

async function handleSelectWatchlist(id: number) {
  await watchlistsStore.fetchWatchlist(id)
}

function promptDeleteWatchlist(id: number) {
  deleteWatchlistId.value = id
}

async function confirmDeleteWatchlist() {
  if (deleteWatchlistId.value === null) {
    return
  }

  await watchlistsStore.deleteWatchlist(deleteWatchlistId.value)
  deleteWatchlistId.value = null
}

async function handleAddItem(payload: {
  notes?: string
  symbol_id: number
}) {
  if (!watchlistsStore.selectedWatchlist) {
    return
  }

  await watchlistsStore.addItem(watchlistsStore.selectedWatchlist.id, payload)
  isAddTickerModalOpen.value = false
}

function promptDeleteItem(itemId: number) {
  deleteTickerId.value = itemId
}

async function confirmDeleteItem() {
  if (!watchlistsStore.selectedWatchlist || deleteTickerId.value === null) {
    return
  }

  await watchlistsStore.deleteItem(watchlistsStore.selectedWatchlist.id, deleteTickerId.value)
  deleteTickerId.value = null
}
</script>

<template>
  <section class="watchlists-page-header card">
    <div>
      <p class="eyebrow">Operator Workspace</p>
      <h2 class="page-title">Watchlists</h2>
      <p class="page-subtitle">
        Keep all lists visible on the left, inspect the selected list on the right, and manage symbols without losing context.
      </p>
    </div>

    <div class="watchlists-page-actions">
      <div class="watchlists-page-meta">
        <div class="meta-stat">
          <span>Total watchlists</span>
          <strong>{{ watchlistsStore.watchlists.length }}</strong>
        </div>
        <div class="meta-stat">
          <span>Selected items</span>
          <strong>{{ selectedItemsCount }}</strong>
        </div>
      </div>

      <div class="watchlists-toolbar-actions">
        <button class="primary-button icon-button" type="button" @click="isCreateModalOpen = true">
          <AppIcon name="Plus" :size="16" />
          <span>New watchlist</span>
        </button>
      </div>
    </div>
  </section>

  <div v-if="watchlistsStore.successMessage" class="feedback-banner success-banner">
    {{ watchlistsStore.successMessage }}
  </div>

  <div v-if="watchlistsStore.error" class="feedback-banner error-banner">
    {{ watchlistsStore.error }}
  </div>

  <section class="watchlists-master-detail">
    <div class="watchlists-sidebar-panel card section-card">
      <WatchlistsSidebar
        :watchlists="watchlistsStore.watchlists"
        :selected-id="watchlistsStore.selectedWatchlist?.id"
        :is-loading="watchlistsStore.isLoading"
        @select="handleSelectWatchlist"
        @delete="promptDeleteWatchlist"
      />
    </div>

    <div class="watchlists-detail-panel card section-card">
      <WatchlistDetail
        :watchlist="watchlistsStore.selectedWatchlist"
        :is-loading="watchlistsStore.isLoading"
        @remove="promptDeleteItem"
      >
        <template #actions>
          <button class="ghost-button icon-button" type="button" @click="isAddTickerModalOpen = true" :disabled="!watchlistsStore.selectedWatchlist">
            <AppIcon name="Plus" :size="16" />
            <span>Add ticker</span>
          </button>
        </template>
      </WatchlistDetail>
    </div>
  </section>

  <BaseModal
    :open="isCreateModalOpen"
    title="Create watchlist"
    description="Each watchlist stays scoped to one market so symbol management stays clean."
    size="md"
    @close="isCreateModalOpen = false"
  >
    <CreateWatchlistForm :is-saving="watchlistsStore.isSaving" @submit="handleCreateWatchlist" />
  </BaseModal>

  <BaseModal
    :open="isAddTickerModalOpen"
    title="Add ticker"
    :description="watchlistsStore.selectedWatchlist ? `Add a symbol to ${watchlistsStore.selectedWatchlist.name}.` : 'Select a watchlist first before adding a symbol.'"
    size="md"
    @close="isAddTickerModalOpen = false"
  >
    <AddWatchlistItemForm
      :disabled="!watchlistsStore.selectedWatchlist"
      :is-saving="watchlistsStore.isSaving"
      :market-type="watchlistsStore.selectedWatchlist?.market_type"
      @submit="handleAddItem"
    />
  </BaseModal>

  <ConfirmModal
    :open="deleteWatchlistId !== null"
    title="Delete watchlist"
    description="This will delete the watchlist and all of its symbols. This action cannot be undone."
    confirm-label="Delete watchlist"
    tone="danger"
    :is-processing="watchlistsStore.isSaving"
    @close="deleteWatchlistId = null"
    @confirm="confirmDeleteWatchlist"
  />

  <ConfirmModal
    :open="deleteTickerId !== null"
    title="Remove ticker"
    description="This will remove the selected ticker from the current watchlist."
    confirm-label="Remove ticker"
    tone="danger"
    :is-processing="watchlistsStore.isSaving"
    @close="deleteTickerId = null"
    @confirm="confirmDeleteItem"
  />
</template>
