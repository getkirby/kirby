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
  base: null,
  page: null,
  swap: null,

  /**
   * Setup call to make Fiber ready
   *
   * @param {object} options
   */
  init({ page, swap, base }) {

    // set the base URL for all requests
    this.base = base || document.querySelector("base").href;

    // callback function which handles
    // swapping components
    this.swap = swap;

    // set initial page
    this.setPage(page);

    // back button event
    window.addEventListener("popstate", this.onPopstateEvent.bind(this));

    // remember scroll position
    document.addEventListener(
      "scroll",
      debounce(this.onScrollEvent.bind(this), 100),
      true
    );
  },

  /**
   * Prepares a set of values to
   * be included in a comma-separated list
   * in a URL query (see include & only params)
   *
   * @param {string|Array} array
   * @returns Array
   */
  arrayToString(array) {
    if (Array.isArray(array) === false) {
      return String(array);
    }
    return array.join(",");
  },

  /**
   * Creates a proper request body
   *
   * @param {string|object}
   * @returns {string}
   */
  body(body) {
    if (typeof body === "object") {
      return JSON.stringify(body);
    }

    return body;
  },

  /**
   * Loads the Vue component for a
   * Fiber view
   *
   * @param {string} name
   * @returns {object}
   */
  component(name) {
    return Vue.component(name);
  },

  /**
   * After a new view response is loaded
   * the props are all processed to set the
   * document title and language. The props are
   * merged with the globals from window.panel
   * to get a full state for the view
   *
   * @param {object} data
   * @returns {object}
   */
  data(data) {
    [
      "$config",
      "$language",
      "$languages",
      "$license",
      "$menu",
      "$multilang",
      "$permissions",
      "$system",
      "$translation",
      "$urls",
      "$user",
      "$view"
    ].forEach((key) => {
      if (data[key] !== undefined) {
        Vue.prototype[key] = window.panel[key] = data[key];
      } else {
        Vue.prototype[key] = data[key] = window.panel[key];
      }
    });

    // set the lang attribute according to the current translation
    if (data.$translation) {
      document.documentElement.lang = data.$translation.code;
    }

    // set the document title according to $view.title
    if (data.$view.title) {
      document.title = data.$view.title + " | " + data.$system.title;
    } else {
      document.title = data.$system.title;
    }

    // return the full data object
    return data;
  },

  /**
   * Sends a view request to load and
   * navigate to a new view
   *
   * @param {string} url
   * @param {object} options
   * @returns {object}
   */
  async go(url, options) {
    options = {
      headers: {},
      only: [],
      preserveScroll: false,
      preserveState: false,
      globals: false,
      silent: false,
      ...options || {}
    };

    // save the current scrolling positions
    // for all scroll regions
    this.saveScroll();

    const globals = this.arrayToString(options.globals);
    const only    = this.arrayToString(options.only);

    let json = await this.request(url, {
      ...options,
      headers: {
        "X-Fiber-Globals": globals,
        "X-Fiber-Only": only,
        ...options.headers
      }
    });

    // add exisiting data to partial requests
    if (only.length) {
      json = merge(this.page, json);
    }

    return this.setPage(json, options);
  },

  /**
   * Handles the browser back button event
   *
   * @param {*} event
   */
  async onPopstateEvent(event) {
    // if a state is included, set the page
    // based on this state (which will cause
    // a swap of components)
    if (event.state !== null) {
      return this.setPage(event.state, { preserveState: false });
    }

    const url = this.url(this.page.$url);

    // otherwise, just make sure to update
    // the state properly
    this.state({ ...this.page, url: url.href });

    // reset the scroll position once the state is replaced
    this.resetScroll();
  },

  /**
   * Saves the scroll position of every area
   * that has the scroll-region attribute
   *
   * @param {*} event
   */
  onScrollEvent(event) {
    if (
      typeof event.target.hasAttribute === "function" &&
      event.target.hasAttribute("scroll-region")
    ) {
      this.saveScroll();
    }
  },

  /**
   * Builds a query string for request URLs
   *
   * @param {object} data
   * @param {object} base
   * @returns {URLSearchParams}
   */
  query(query = {}, base = {}) {
    let params = new URLSearchParams(base);

    // make sure that we always work with a data object
    if (typeof query !== "object") {
      query = {};
    }

    // add all data params unless they are empty/null
    Object.entries(query).forEach(([key, value]) => {
      if (value !== null) {
        params.set(key, value);
      }
    });

    return params;
  },

  /**
   * A wrapper around go() which
   * reloads the current URL
   *
   * @param {object} options
   * @returns {object}
   */
  reload(options = {}) {
    return this.go(window.location.href, {
      ...options,
      preserveScroll: true,
      preserveState: true
    });
  },

  /**
   * Sends a generic Fiber request
   *
   * @param {string|URL} path
   * @param {Object} options
   * @returns {Object}
   */
  async request(path, options = {}) {
    options = {
      method: "GET",
      query: {},
      silent: false,
      ...options
    };

    document.dispatchEvent(new CustomEvent("fiber.start", { detail: options }));

    try {
      const url      = this.url(path, options.query);
      const response = await fetch(url, {
        method: options.method,
        body: this.body(options.body),
        headers: {
          "X-Fiber": true,
          "X-Fiber-Referrer": this.page.$view.path,
          ...options.headers,
        }
      });

      return await toJson(response);
    } finally {
      document.dispatchEvent(new Event("fiber.finish"));
    }

  },

  /**
   * Moves the scroll position of every
   * scroll region back to the top
   */
  resetScroll() {
    // update the scroll position of the document
    document.documentElement.scrollTop = 0;
    document.documentElement.scrollLeft = 0;

    // update the scroll position of each region
    this.scrollRegions().forEach((region) => {
      region.scrollTop = 0;
      region.scrollLeft = 0;
    });

    // resave the restored scroll position
    this.saveScroll();
  },

  /**
   * Restores the previously saved scroll
   * positions for every scroll region
   */
  restoreScroll() {
    if (this.page.scrollRegions) {
      this.scrollRegions().forEach((region, index) => {
        region.scrollTop = this.page.scrollRegions[index].top;
        region.scrollLeft = this.page.scrollRegions[index].left;
      });
    }
  },

  /**
   * Saves the scroll position for every
   * scroll region
   */
  saveScroll() {
    const regions = Array.from(this.scrollRegions());
    this.state({
      ...this.page,
      scrollRegions: regions.map((region) => ({
        top: region.scrollTop,
        left: region.scrollLeft
      }))
    });
  },

  /**
   * Fetches all DOM elements with
   * the scroll region attribute
   *
   * @returns NodeList
   */
  scrollRegions() {
    return document.querySelectorAll("[scroll-region]");
  },

  /**
   * Stores the state for the current page
   *
   * @param {object} page
   * @param {object} options
   */
  async setPage(page, {
    replace = false,
    preserveScroll = false,
    preserveState = false
  } = {}) {
    // resolve component
    const component = await this.component(page.$view.component);

    // get all scroll regions
    page.scrollRegions = page.scrollRegions || [];

    // either replacing the whole state
    // or pushing onto it
    if (replace || this.url(page.$url).href === window.location.href) {
      this.state(page);
    } else {
      this.state(page, "push");
    }

    // clone existing data
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

  /**
   * Updates the browser history and the page
   * object with the current state
   *
   * @param {object} page
   * @param {string} action
   */
  state(page, action = "replace") {
    this.page = page;
    window.history[action + "State"](page, "", page.$url);
  },

  /**
   * Builds a full URL object based on the
   * given path or another URL object and query data
   *
   * @param {string|URL} url
   * @param {Object} query
   * @returns
   */
  url(url = "", query = {}) {
    if (typeof url === "string" && url.match(/^https?:\/\//) === null) {
      url = new URL(this.base + url.replace(/^\//, ""));
    } else {
      url = new URL(url);
    }

    url.search = this.query(query, url.search);
    return url;
  },

};

export const plugin = {
  install(Vue) {
    Vue.prototype.$dialog = async function (path, options = {}) {
      try {
        const data = await Fiber.request("dialogs/" + path, options);

        // the GET request for the dialog is failing
        if (!data.$dialog) {
          throw `The dialog could not be loaded`;
        }

        // the dialog sends a backend error
        if (data.$dialog.error) {
          throw data.$dialog.error;
        }

        // open the dialog and keep the dialog props in the store
        this.$store.dispatch("dialog", data.$dialog);

        // return the dialog object if needed
        return data.$dialog;
      } catch (e) {
        console.error(e);
        this.$store.dispatch("notification/error", e);
      }
    };

    Vue.prototype.$go = window.panel.$go = function (path, options) {
      return Fiber.go(this.$url(path), options);
    };

    Vue.prototype.$reload = window.panel.$reload = function (options) {
      return Fiber.reload(options);
    };

    Vue.prototype.$request = async function (...args) {
      return await Fiber.request(...args);
    };

    Vue.prototype.$url = function (...args) {
      return Fiber.url(...args);
    };
  }
};

export const component = {
  name: "Fiber",
  data() {
    return {
      component: null,
      page: window.fiber,
      key: null
    };
  },
  created() {
    Fiber.init({
      page: window.fiber,
      swap: async ({ component, page, preserveState }) => {
        this.component = component;
        this.page = page;
        this.key = preserveState ? this.key : Date.now();
      }
    });
  },
  render(h) {
    if (this.component) {
      return h(this.component, {
        key: this.key,
        props: this.page.$view.props
      });
    }
  }
};
