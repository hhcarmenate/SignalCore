import { mount, type VueWrapper } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import WatchlistsView from '../WatchlistsView.vue'

const mocks = vi.hoisted(() => ({
  fetchWatchlists: vi.fn(),
  fetchWatchlist: vi.fn(),
  createWatchlist: vi.fn(),
  deleteWatchlist: vi.fn(),
  addItem: vi.fn(),
  deleteItem: vi.fn(),
  searchSymbols: vi.fn(async () => ({
    data: [
      {
        id: 88,
        asset_type: 'stock',
        symbol: 'AAPL',
        name: 'Apple Inc.',
        market: 'us_equities',
        exchange: 'NASDAQ',
        status: 'active',
        currency: 'USD',
        provider: 'manual',
        provider_symbol: 'AAPL',
      },
    ],
  })),
}))

const storeState = {
  watchlists: [
    {
      id: 1,
      name: 'Core Momentum',
      description: 'Primary names',
      market_type: 'us_equities',
      is_active: true,
      items_count: 1,
    },
  ],
  selectedWatchlist: {
    id: 1,
    name: 'Core Momentum',
    description: 'Primary names',
    market_type: 'us_equities',
    is_active: true,
    items: [
      {
        id: 10,
        watchlist_id: 1,
        symbol_id: 50,
        notes: 'High priority',
        symbol: {
          id: 50,
          asset_type: 'stock',
          symbol: 'NVDA',
          name: 'NVIDIA Corp',
          market: 'us_equities',
          exchange: 'NASDAQ',
          status: 'active',
          currency: 'USD',
          provider: 'manual',
          provider_symbol: 'NVDA',
        },
      },
    ],
  },
  isLoading: false,
  isSaving: false,
  error: null,
  successMessage: null,
}

vi.mock('../../stores/watchlists', () => ({
  useWatchlistsStore: () => ({
    ...storeState,
    fetchWatchlists: mocks.fetchWatchlists,
    fetchWatchlist: mocks.fetchWatchlist,
    createWatchlist: mocks.createWatchlist,
    deleteWatchlist: mocks.deleteWatchlist,
    addItem: mocks.addItem,
    deleteItem: mocks.deleteItem,
    clearMessages: vi.fn(),
  }),
}))

vi.mock('../../lib/symbols', () => ({
  searchSymbols: mocks.searchSymbols,
}))

function mountView(): VueWrapper {
  return mount(WatchlistsView, {
    attachTo: document.body,
  })
}

describe('WatchlistsView', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
    vi.stubGlobal('confirm', vi.fn(() => true))
    document.body.innerHTML = ''
  })

  it('loads watchlists on mount and renders key sections', () => {
    const wrapper = mountView()

    expect(mocks.fetchWatchlists).toHaveBeenCalled()
    expect(wrapper.text()).toContain('Watchlists')
    expect(wrapper.text()).toContain('Core Momentum')
    expect(wrapper.text()).toContain('Add ticker')
    expect(wrapper.text()).toContain('us_equities')
  })

  it('creates a watchlist from the form submission', async () => {
    const wrapper = mountView()

    await wrapper.find('button.primary-button').trigger('click')

    const createInput = document.body.querySelector('input[placeholder="Core momentum"]') as HTMLInputElement | null
    const createForm = document.body.querySelector('form') as HTMLFormElement | null

    expect(createInput).toBeTruthy()
    expect(createForm).toBeTruthy()

    createInput!.value = 'Growth Names'
    createInput!.dispatchEvent(new Event('input'))
    createForm!.dispatchEvent(new Event('submit'))

    expect(mocks.createWatchlist).toHaveBeenCalledWith({
      name: 'Growth Names',
      description: undefined,
      market_type: 'us_equities',
      is_active: true,
    })
  })

  it('selects a watchlist from the sidebar', async () => {
    const wrapper = mountView()
    const watchlistButton = wrapper.find('.watchlist-list-item')

    await watchlistButton.trigger('click')

    expect(mocks.fetchWatchlist).toHaveBeenCalledWith(1)
  })

  it('opens the add ticker modal with the autocomplete field', async () => {
    const wrapper = mountView()

    const addTickerButton = wrapper
      .findAll('button')
      .find((button) => button.text().includes('Add ticker'))

    expect(addTickerButton).toBeTruthy()
    await addTickerButton!.trigger('click')

    const symbolInput = document.body.querySelector('input[placeholder="Search AAPL, SPY, NVIDIA…"]') as HTMLInputElement | null

    expect(symbolInput).toBeTruthy()
  })

  it('deletes a watchlist after confirmation', async () => {
    const wrapper = mountView()

    await wrapper.find('.danger-link').trigger('click')

    const confirmButton = Array.from(document.body.querySelectorAll('button')).find((button) =>
      button.textContent?.includes('Delete watchlist'),
    ) as HTMLButtonElement | undefined

    expect(confirmButton).toBeTruthy()
    confirmButton!.click()

    expect(mocks.deleteWatchlist).toHaveBeenCalledWith(1)
  })

  it('deletes an item after confirmation', async () => {
    const wrapper = mountView()
    const removeButtons = wrapper.findAll('button')
    const removeItemButton = removeButtons.find((button) => button.text() === 'Remove')

    expect(removeItemButton).toBeTruthy()
    await removeItemButton!.trigger('click')

    const confirmButton = Array.from(document.body.querySelectorAll('button')).find((button) =>
      button.textContent?.includes('Remove ticker'),
    ) as HTMLButtonElement | undefined

    expect(confirmButton).toBeTruthy()
    confirmButton!.click()

    expect(mocks.deleteItem).toHaveBeenCalledWith(1, 10)
  })
})
