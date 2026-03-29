import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import BaseAutocomplete from '../BaseAutocomplete.vue'

describe('BaseAutocomplete', () => {
  beforeEach(() => {
    vi.useFakeTimers()
  })

  it('debounces searches and emits the selected option', async () => {
    const search = vi.fn(async () => [
      {
        id: 1,
        label: 'AAPL',
        description: 'Apple Inc.',
      },
    ])

    const wrapper = mount(BaseAutocomplete, {
      props: {
        label: 'Symbol',
        search,
      },
      attachTo: document.body,
    })

    const input = wrapper.find('input')

    await input.trigger('focus')
    await input.setValue('AAP')
    await vi.advanceTimersByTimeAsync(100)
    await input.setValue('AAPL')
    await vi.advanceTimersByTimeAsync(300)

    expect(search).toHaveBeenCalledTimes(1)
    expect(search).toHaveBeenCalledWith('AAPL')

    const option = wrapper.find('.autocomplete-option')
    expect(option.exists()).toBe(true)

    await option.trigger('mousedown')

    expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toMatchObject({
      id: 1,
      label: 'AAPL',
    })
  })

})
