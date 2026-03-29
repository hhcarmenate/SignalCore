import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

import { api } from '../lib/api'
import type {
  CreateWatchlistItemPayload,
  CreateWatchlistPayload,
  Watchlist,
  WatchlistDetailResponse,
  WatchlistListResponse,
} from '../types/watchlists'

export const useWatchlistsStore = defineStore('watchlists', () => {
  const watchlists = ref<Watchlist[]>([])
  const selectedWatchlist = ref<Watchlist | null>(null)
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref<string | null>(null)
  const successMessage = ref<string | null>(null)

  const hasWatchlists = computed(() => watchlists.value.length > 0)

  async function fetchWatchlists(preferredId?: number) {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.get<WatchlistListResponse>('/watchlists')
      watchlists.value = response.data

      const nextSelectedId =
        preferredId ?? selectedWatchlist.value?.id ?? response.data[0]?.id

      if (nextSelectedId) {
        await fetchWatchlist(nextSelectedId)
      } else {
        selectedWatchlist.value = null
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not load watchlists.'
    } finally {
      isLoading.value = false
    }
  }

  async function fetchWatchlist(id: number) {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.get<WatchlistDetailResponse>(`/watchlists/${id}`)
      selectedWatchlist.value = response.data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not load watchlist.'
    } finally {
      isLoading.value = false
    }
  }

  async function createWatchlist(payload: CreateWatchlistPayload) {
    isSaving.value = true
    error.value = null
    successMessage.value = null

    try {
      const response = await api.post<WatchlistDetailResponse>('/watchlists', payload)
      successMessage.value = `Watchlist "${response.data.name}" created.`
      await fetchWatchlists(response.data.id)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not create watchlist.'
      throw err
    } finally {
      isSaving.value = false
    }
  }

  async function deleteWatchlist(id: number) {
    isSaving.value = true
    error.value = null
    successMessage.value = null

    try {
      await api.delete(`/watchlists/${id}`)
      successMessage.value = 'Watchlist deleted.'
      const fallbackId = watchlists.value.find((watchlist) => watchlist.id !== id)?.id
      if (selectedWatchlist.value?.id === id) {
        selectedWatchlist.value = null
      }
      await fetchWatchlists(fallbackId)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not delete watchlist.'
      throw err
    } finally {
      isSaving.value = false
    }
  }

  async function addItem(watchlistId: number, payload: CreateWatchlistItemPayload) {
    isSaving.value = true
    error.value = null
    successMessage.value = null

    try {
      await api.post(`/watchlists/${watchlistId}/items`, payload)
      successMessage.value = 'Watchlist item added.'
      await fetchWatchlists(watchlistId)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not add watchlist item.'
      throw err
    } finally {
      isSaving.value = false
    }
  }

  async function deleteItem(watchlistId: number, itemId: number) {
    isSaving.value = true
    error.value = null
    successMessage.value = null

    try {
      await api.delete(`/watchlists/${watchlistId}/items/${itemId}`)
      successMessage.value = 'Watchlist item removed.'
      await fetchWatchlists(watchlistId)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Could not delete watchlist item.'
      throw err
    } finally {
      isSaving.value = false
    }
  }

  function clearMessages() {
    error.value = null
    successMessage.value = null
  }

  return {
    watchlists,
    selectedWatchlist,
    isLoading,
    isSaving,
    error,
    successMessage,
    hasWatchlists,
    fetchWatchlists,
    fetchWatchlist,
    createWatchlist,
    deleteWatchlist,
    addItem,
    deleteItem,
    clearMessages,
  }
})
