import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import WatchlistDetail from '../WatchlistDetail.vue'

describe('WatchlistDetail', () => {
  it('renders an empty state when no watchlist is selected', () => {
    const wrapper = mount(WatchlistDetail, {
      props: {
        watchlist: null,
        isLoading: false,
      },
    })

    expect(wrapper.text()).toContain('No watchlist selected')
  })

  it('renders watchlist items and market type and emits remove', async () => {
    const wrapper = mount(WatchlistDetail, {
      props: {
        isLoading: false,
        watchlist: {
          id: 1,
          name: 'Core Momentum',
          description: 'Primary names',
          market_type: 'us_equities',
          is_active: true,
          items: [
            {
              id: 10,
              watchlist_id: 1,
              symbol_id: 1,
              notes: 'Watch closely',
              symbol: {
                id: 1,
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
      },
    })

    expect(wrapper.text()).toContain('NVDA')
    expect(wrapper.text()).toContain('Watch closely')
    expect(wrapper.text()).toContain('us_equities')

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('remove')).toEqual([[10]])
  })
})
