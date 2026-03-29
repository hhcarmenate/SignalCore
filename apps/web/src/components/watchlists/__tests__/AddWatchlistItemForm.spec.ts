import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import AddWatchlistItemForm from '../AddWatchlistItemForm.vue'

const mocks = vi.hoisted(() => ({
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

vi.mock('../../../lib/symbols', () => ({
  searchSymbols: mocks.searchSymbols,
}))

describe('AddWatchlistItemForm', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
  })

  it('searches the catalog and emits symbol_id on submit', async () => {
    const wrapper = mount(AddWatchlistItemForm, {
      props: {
        marketType: 'us_equities',
      },
      attachTo: document.body,
    })

    const input = wrapper.find('input[placeholder="Search AAPL, SPY, NVIDIA…"]')

    await nextTick()
    expect(document.activeElement === input.element || document.activeElement === document.body).toBe(true)

    await input.trigger('focus')
    await input.setValue('AAPL')
    await vi.advanceTimersByTimeAsync(300)

    expect(mocks.searchSymbols).toHaveBeenCalled()

    const option = wrapper.find('.autocomplete-option')
    expect(option.exists()).toBe(true)

    await option.trigger('mousedown')
    await nextTick()

    await wrapper.find('form').trigger('submit')

    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          notes: undefined,
          symbol_id: 88,
        },
      ],
    ])
  })

  it('shows catalog-only guidance when there are no results', async () => {
    mocks.searchSymbols.mockResolvedValueOnce({ data: [] })

    const wrapper = mount(AddWatchlistItemForm, {
      props: {
        marketType: 'us_equities',
      },
      attachTo: document.body,
    })

    const input = wrapper.find('input[placeholder="Search AAPL, SPY, NVIDIA…"]')

    await input.setValue('ZZZZ')
    await vi.advanceTimersByTimeAsync(300)

    expect(wrapper.text()).toContain('Manual adds are disabled')
    expect(wrapper.text()).toContain('Only symbols already in the shared catalog can be added')
  })
})
