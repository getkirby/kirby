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

import clone from "../helpers/clone";
import { merge } from "../helpers/object";
import { toJson } from "../api/request";

export default {
  options: {},
  state: {},

  /**
   * Setup call to make Fiber ready
   *
   * @param {object} state
   * @param {object} options
   */
  init(state, options = {}) {
    // defaults
    this.options = {
      base: document.querySelector("base").href,
      headers: () => {},
      onFinish: () => {},
      onStart: () => {},
      onSwap: () => {},
      query: () => {},
      ...options
    };

    // set initial state
    this.setState(state);

    // back button event
    window.addEventListener("popstate", this.reload.bind(this));
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
   * Sends a view request to load and
   * navigate to a new view
   *
   * @param {string} url
   * @param {object} options
   * @returns {object}
   */
  async go(url, options) {
    const json = await this.request(url, options);
    return this.setState(json, options);
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

    // add globals
    Object.entries(this.options.query()).forEach(([key, value]) => {
      value = params.get(key) ?? value ?? null;

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
      replace: true
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
      globals: false,
      method: "GET",
      only: [],
      query: {},
      silent: false,
      ...options
    };

    const globals = this.arrayToString(options.globals);
    const only = this.arrayToString(options.only);

    this.options.onStart(options);

    try {
      const url = this.url(path, options.query);
      const response = await fetch(url, {
        method: options.method,
        body: this.body(options.body),
        credentials: "same-origin",
        cache: "no-store",
        headers: {
          ...this.options.headers(),
          "X-Fiber": true,
          "X-Fiber-Globals": globals,
          "X-Fiber-Only": only,
          "X-Fiber-Referrer": this.state.$view.path,
          ...options.headers
        }
      });

      let json = await toJson(response);

      // add exisiting data to partial requests
      if (only.length) {
        json = merge(this.state, json);
      }

      return json;
    } finally {
      this.options.onFinish(options);
    }
  },

  /**
   * Stores the state for the current page/view
   *
   * @param {object} state
   * @param {object} options
   */
  async setState(state, options = {}) {
    // clone existing data
    this.state = clone(state);

    // either replacing the whole state
    // or pushing onto it
    if (
      options.replace === true ||
      this.url(this.state.$url).href === window.location.href
    ) {
      window.history.replaceState(this.state, "", this.state.$url);
    } else {
      window.history.pushState(this.state, "", this.state.$url);
    }

    this.options.onSwap(state, options);
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
      url = new URL(this.options.base + url.replace(/^\//, ""));
    } else {
      url = new URL(url);
    }

    url.search = this.query(query, url.search);
    return url;
  }
};
