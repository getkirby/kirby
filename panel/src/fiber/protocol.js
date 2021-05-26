import debounce from '../helpers/debounce.js'
import { merge } from '../helpers/object.js'
import { toJson } from '../api/request.js'

export default {
  page: null,
  props: null,
  swap: null,
  component: null,

  init({ page, component, swap, props }) {
    // callback function that receives the
    // component name and returns the Vue component
    this.component = component

    // callback function that resolves the props;
    // we use this to set our globals
    this.props = props

    // callback function which handles
    // swapping components
    this.swap = swap

    // set initial page
    page.url += window.location.hash
    this.setPage(page)

    // set up event listeners
    window.addEventListener('popstate', this.onPopstateEvent.bind(this))
    document.addEventListener('scroll', debounce(this.onScrollEvent.bind(this), 100), true)
  },

  async onPopstateEvent(event) {
    // if a state is included, set the page
    // based on this state (which will cause
    // a swap of components)
    if (event.state !== null) {
      return this.setPage(event.state, { preserveState: false })
    }

    // otherwise, just make sure to update
    // the state properly
    const url = this.toUrl(this.page.url)
    url.hash  = window.location.hash
    this.state({ ...this.page, url: url.href })
    this.resetScroll()
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

  reload(options = {}) {
    return this.visit(window.location.href, {
      ...options,
      preserveScroll: true,
      preserveState: true
    })
  },

  async setPage(page, { replace = false, preserveScroll = false, preserveState = false } = {}) {
    // resolve component
    const component = await this.component(page.component)
    page.scrollRegions = page.scrollRegions || []

    // either replacing the whole state
    // or pushing onto it
    if (replace || this.toUrl(page.url).href === window.location.href) {
      this.state(page)
    } else {
      this.state(page, "push")
    }

    // swap component
    const clone = JSON.parse(JSON.stringify(page))
    clone.props = this.props(clone.props)
    await this.swap({ component, page: clone, preserveState })

    if (!preserveScroll) {
      this.resetScroll()
    }
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
    data = {}
  } = {}) {
    this.saveScroll()
    document.dispatchEvent(new Event('fiber:start'))

    url        = this.toUrl(url);
    url.search = this.toQuery(url.search, data);

    try {
      // fetch the response (only GET request supported)
      const response = await fetch(url, {
        method: "get",
        headers: {
          ...headers,
          Accept: 'text/html, application/xhtml+xml',
          'X-Requested-With': 'XMLHttpRequest',
          'X-Fiber': true,
          ...(only.length ? {
            'X-Fiber-Component': this.page.component,
            'X-Fiber-Partial': only.join(','),
          } : {}),
          ...(this.page.version ? { 'X-Fiber-Version': this.page.version } : {}),
        }
      })

      // turn into data
      const data = await toJson(response)

      // add exisiting data to partial requests
      if (only.length && data.component === this.page.component) {
        data.props = merge(this.page.props, data.props)
      }

      // add hash to response URL if current
      // window URL has hash included
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
      document.dispatchEvent(new Event('fiber:finish'))
    }
  },

  toQuery(search, data) {
    let params = new URLSearchParams(search);

    if (typeof data !== "object") {
      data = {};
    }

    // add all data params unless they are empty/null
    Object.entries(data).forEach(([key, value]) => {
      if (value !== null) {
        params.set(key, value);
      }
    });

    return params;
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
