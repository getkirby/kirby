/**
 * The code in this file is inspired by and partly based on Inertia.js 
 * (https://github.com/inertiajs/inertia) which has been released under
 * the following MIT License:
 * 
 * Copyright (c) Jonathan Reinink <jonathan@reinink.ca>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

import Vue from "vue";
import clone from "../helpers/clone.js";
import debounce from "../helpers/debounce.js";
import { merge } from "../helpers/object.js";
import { toJson } from "../api/request.js";

const Fiber = {
  page: null,
  swap: null,

  init({ page, swap }) {
    // callback function which handles
    // swapping components
    this.swap = swap;

    // add the hash to the url if it exists
    page.$url += window.location.hash;

    // set initial page
    this.setPage(page);

    // back button event
    window.addEventListener("popstate", this.onPopstateEvent.bind(this));

    // remember scroll position
    document.addEventListener("scroll", debounce(this.onScrollEvent.bind(this), 100), true);
  },

  component(name) {
    return Vue.component(name);
  },

  async onPopstateEvent(event) {
    // if a state is included, set the page
    // based on this state (which will cause
    // a swap of components)
    if (event.state !== null) {
      return this.setPage(event.state, { preserveState: false });
    }

    // otherwise, just make sure to update
    // the state properly
    const url = this.toUrl(this.page.$url);

    // add the hash if it exists
    url.hash = window.location.hash;

    this.state({ ...this.page, url: url.href });
    this.resetScroll();
  },

  onScrollEvent(event) {
    if (
      typeof event.target.hasAttribute === "function" &&
      event.target.hasAttribute("scroll-region")
    ) {
      this.saveScroll();
    }
  },

  data(data) {

    // Add data to the Vue prototype
    // and window.panel object if the
    // key exists. Otherwise take from
    // the existing window.panel object
    [
      "$areas",
      "$config",
      "$language",
      "$languages",
      "$license",
      "$multilang",
      "$permissions",
      "$system",
      "$translation",
      "$urls",
      "$user",
      "$view"
    ].forEach(key => {
      if (data[key] !== undefined) {
        Vue.prototype[key] = window.panel[key] = data[key];
      } else {
        Vue.prototype[key] = data[key] = window.panel[key];
      }
    });

    // set the lang attribute according to the current translation
    if (data.$translation) {
      document.querySelector("html").setAttribute("lang", data.$translation.code);
    }

    // set the document title according to $view.title
    if (data.$view.title) {
      document.title = data.$view.title + " | Kirby Panel";
    } else {
      document.title = "Kirby Panel";
    }

    // return the full data object
    return data;
  },

  reload(options = {}) {
    return this.visit(window.location.href, {
      ...options,
      preserveScroll: true,
      preserveState: true
    });
  },

  resetScroll() {
    // update the scroll position of the document
    document.documentElement.scrollTop = 0;
    document.documentElement.scrollLeft = 0;

    // update the scroll position of each region
    this.scrollRegions().forEach(region => {
      region.scrollTop  = 0;
      region.scrollLeft = 0;
    })

    // resave the restored scroll position
    this.saveScroll();

    // if a hash exists, scroll the matching element into view
    if (window.location.hash) {
      document.getElementById(window.location.hash.slice(1))?.scrollIntoView();
    }
  },

  restoreScroll() {
    if (this.page.scrollRegions) {
      this.scrollRegions().forEach((region, index) => {
        region.scrollTop  = this.page.scrollRegions[index].top;
        region.scrollLeft = this.page.scrollRegions[index].left;
      })
    }
  },

  saveScroll() {
    const regions = Array.from(this.scrollRegions());
    this.state({
      ...this.page,
      scrollRegions: regions.map(region => ({
        top:  region.scrollTop,
        left: region.scrollLeft,
      })),
    });
  },

  scrollRegions() {
    return document.querySelectorAll("[scroll-region]");
  },

  async setPage(page, { replace = false, preserveScroll = false, preserveState = false } = {}) {
    // resolve component
    const component = await this.component(page.$view.component);

    // get all scroll regions
    page.scrollRegions = page.scrollRegions || [];

    // either replacing the whole state
    // or pushing onto it
    if (replace || this.toUrl(page.$url).href === window.location.href) {
      this.state(page);
    } else {
      this.state(page, "push");
    }

    // swap component
    let data = clone(page);

    // apply all data
    data = this.data(data);

    // call and wait for the swap callback
    await this.swap({ component, page: data, preserveState });

    // reset scrolling if it should not be preserved
    if (!preserveScroll) {
      this.resetScroll();
    }
  },

  state(page, action = "replace") {
    this.page = page;
    window.history[action + "State"](page, "", page.$url);
  },

  toQuery(search, data) {
    let params = new URLSearchParams(search);

    // make sure that we always work with a data object
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

  toUrl(href, {
    data = {},
    hash = true
  } = {}) {
    let url

    if (hash === true) {
      url = new URL(href, window.location);
    } else {
      url = new URL(href);
      url.hash = "";
    }

    url.search = this.toQuery(url.search, data);

    return url
  },

  async visit(url, {
    replace = false,
    preserveScroll = false,
    preserveState = false,
    only = [],
    headers = {},
    data = {}
  } = {}) {

    // save the current scrolling positions
    // for all scroll regions
    this.saveScroll();

    document.dispatchEvent(new Event("fiber:start"));

    // make sure only is an array
    if (Array.isArray(only) === false) {
      only = [only]
    }

    // create proper URL
    url = this.toUrl(url, { data: data || {}, hash: false });

    try {
      // fetch the response (only GET request supported)
      const response = await fetch(url, {
        method: "get",
        headers: {
          ...headers,
          "Accept": "text/html, application/xhtml+xml",
          "X-Requested-With": "XMLHttpRequest",
          "X-Fiber": true,
          ...(only.length ? {
            "X-Fiber-Component": this.page.$view.component,
            "X-Fiber-Include": only.join(","),
          } : {}),
        }
      });

      // turn into json data
      let json = await toJson(response);

      // add exisiting data to partial requests
      if (only.length) {
        json = merge(this.page, json);
      }

      // add hash to response URL if current
      // window URL has hash included
      const responseUrl = this.toUrl(json.$url);

      if (
        url.hash &&
        !responseUrl.hash &&
        this.toUrl(json.$url, { hash: false }).href === responseUrl.href
      ) {
        responseUrl.hash = url.hash;
        json.$url = responseUrl.href;
      }

      return this.setPage(json, { replace, preserveScroll, preserveState });

    } catch (error) {
      console.error(error);

    } finally {
      document.dispatchEvent(new Event("fiber:finish"));
    }
  }
}

export const plugin = {
  install(Vue) {
    Vue.prototype.$url = function (path = "") {
      // pass window.location objects without modification
      if (typeof path === "object") {
        return path;
      }

      return document.querySelector("base").href + path.replace(/^\//, "")
    }
    Vue.prototype.$go = window.panel.$go = function (path, options) {
      return Fiber.visit(this.$url(path), options)
    }
    Vue.prototype.$reload = window.panel.$reload = function (options) {
      if (typeof options === "string") {
        options = { only: [options] };
      }
      return Fiber.reload(options)
    }
  },
}

export const component = {
  name: "Fiber",
  data() {
    return {
      component: null,
      page: window.fiber,
      key: null,
    };
  },
  created() {
    Fiber.init({
      page: window.fiber,
      swap: async ({ component, page, preserveState }) => {
        this.component = component;
        this.page = page;
        this.key = preserveState ? this.key : Date.now();
      },
    })
  },
  render(h) {
    if (this.component) {
      return h(this.component, {
        key: this.key,
        props: this.page.$view.props
      });
    }
  }
}
