import debounce from '../helpers/debounce.js'

export default {
  id: null,
  page: null,
  props: null,
  swap: null,
  component: null,

  init({ page, component, swap, props }) {
    this.component = component
    this.props     = props
    this.swap      = swap

    // set initial page
    if (this.isBackForwardVisit()) {
      this.setBackForwardVisit(page)
    } else {
      page.url += window.location.hash
      this.setPage(page)
    }
    
    // set up event listeners
    window.addEventListener('popstate', this.onPopstateEvent.bind(this))
    document.addEventListener('scroll', debounce(this.onScrollEvent.bind(this), 100), true)
  },

  isBackForwardVisit() {
    return window.history.state
      && window.performance
      && window.performance.getEntriesByType('navigation').length
      && window.performance.getEntriesByType('navigation')[0].type === 'back_forward'
  },

  async onPopstateEvent(event) {
    if (event.state !== null) {
      this.setPage(event.state, { preserveState: false })

    } else {
      const url = this.toUrl(this.page.url)
      url.hash  = window.location.hash
      this.state({ ...this.page, url: url.href })
      this.resetScroll()
    }
  },

  onScrollEvent(event) {
    if (
      typeof event.target.hasAttribute === 'function' && 
      event.target.hasAttribute('scroll-region')
    ) {
      this.saveScroll()
    }
  },

  scrollRegions() {
    return document.querySelectorAll('[scroll-region]')
  },

  saveScroll() {
    const regions = Array.prototype.slice.call(this.scrollRegions());
    this.state({
      ...this.page,
      scrollRegions: regions.map(region => ({
        top:  region.scrollTop,
        left: region.scrollLeft,
      })),
    })
  },

  resetScroll() {
    document.documentElement.scrollTop = 0
    document.documentElement.scrollLeft = 0
    this.scrollRegions().forEach(region => {
      region.scrollTop  = 0
      region.scrollLeft = 0
    })
    this.saveScroll()

    if (window.location.hash) {
      document.getElementById(window.location.hash.slice(1))?.scrollIntoView()
    }
  },

  restoreScroll() {
    if (this.page.scrollRegions) {
      this.scrollRegions().forEach((region, index) => {
        region.scrollTop  = this.page.scrollRegions[index].top
        region.scrollLeft = this.page.scrollRegions[index].left
      })
    }
  },

  async setBackForwardVisit(page) {
    window.history.state.version = page.version
    await this.setPage(window.history.state, { preserveScroll: true });
    this.restoreScroll();
  },

  setId() {
    this.id = {}
    return this.id
  },

  resolve(value, page) {
    if (typeof value === 'function') {
      return value(page)
    }
    if (value === 'errors') {
      return Object.keys(page.props.errors || {}).length > 0
    }
    return value
  },

  async setPage(page, { id = this.setId(), replace = false, preserveScroll = false, preserveState = false } = {}) {
    const component = await this.component(page.component)

    if (id === this.id) {
      page.scrollRegions = page.scrollRegions || []

      if (replace || this.toUrl(page.url).href === window.location.href) {
        this.state(page)
      } else {
        this.state(page, "push")
      }

      const clone = JSON.parse(JSON.stringify(page))
      clone.props = this.props(clone.props)
      await this.swap({ component, page: clone, preserveState })

      if (!preserveScroll) {
        this.resetScroll()
      }
    }
  },
 
  reload(options = {}) {
    return this.visit(window.location.href, {
      ...options, 
      preserveScroll: true, 
      preserveState: true
    })
  },

  state(page, action = "replace") {
    this.page = page
    window.history[action + "State"](page, '', page.url)
  },

  async visit(url, {
    replace = false,
    preserveScroll = false,
    preserveState = false,
    only = [],
    headers = {},
  } = {}) {

    this.saveScroll()
    document.dispatchEvent(new Event('inertia:start'))

    try {
      const response = await fetch(this.toUrl(url, false), {
        method: "get",
        headers: {
          ...headers,
          Accept: 'text/html, application/xhtml+xml',
          'X-Requested-With': 'XMLHttpRequest',
          'X-Inertia': true,
          ...(only.length ? {
            'X-Inertia-Partial-Component': this.page.component,
            'X-Inertia-Partial-Data': only.join(','),
          } : {}),
          ...(this.page.version ? { 'X-Inertia-Version': this.page.version } : {}),
        }
      })

      const data = await response.json()

      if (only.length && data.component === this.page.component) {
        data.props = { ...this.page.props, ...data.props }
      }

      preserveScroll = this.resolve(preserveScroll, data)
      preserveState  = this.resolve(preserveState, data)

      const responseUrl = this.toUrl(data.url)

      if (
        url.hash && 
        !responseUrl.hash && 
        this.toUrl(data.url, false).href === responseUrl.href
      ) {
        responseUrl.hash = url.hash
        data.url = responseUrl.href
      }

      return this.setPage(data, { replace, preserveScroll, preserveState })

    } catch (error) {
      console.error(error)

    } finally {
      document.dispatchEvent(new Event('inertia:finish'))
    }
  },

  toUrl(href, hash = true) {

    if (hash === true) {
      return new URL(href, window.location)
    }

    const url = new URL(href)
    url.hash = ''
    return url
  }
}
