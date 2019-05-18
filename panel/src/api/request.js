import api from "./api.js";
import store from "@/store/store.js";

export default {
  running: 0,
  request(path, options, silent = false) {
    options = Object.assign(options || {}, {
      credentials: "same-origin",
      cache: "no-cache",
      headers: {
        "x-requested-with": "xmlhttprequest",
        "content-type": "application/json",
        ...options.headers,
      }
    });

    if (store.state.languages.current) {
      options.headers["x-language"] = store.state.languages.current.code;
    }

    // add the csrf token to every request if it has been set
    options.headers["x-csrf"] = window.panel.csrf;

    // create a request id
    const id = path + "/" + JSON.stringify(options);

    api.config.onStart(id, silent);
    this.running++;

    return fetch(api.config.endpoint + "/" + path, options)
      .then(response => {
        return response.text();
      })
      .then(text => {
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error("The JSON response from the API could not be parsed. Please check your API connection.");
        }
      })
      .then(json => {
        if (json.status && json.status === "error") {
          throw json;
        }

        let response = json;

        if (json.data && json.type && json.type === "model") {
          response = json.data;
        }

        this.running--;
        api.config.onComplete(id);
        api.config.onSuccess(json);
        return response;
      })
      .catch(error => {
        this.running--;
        api.config.onComplete(id);
        api.config.onError(error);
        throw error;
      });
  },
  get(path, query, options, silent = false) {
    if (query) {
      path +=
        "?" +
        Object.keys(query)
          .map(key => key + "=" + query[key])
          .join("&");
    }

    return this.request(path, Object.assign(options || {}, { method: "GET" }), silent);
  },
  post(path, data, options, method = "POST", silent = false) {
    return this.request(
      path,
      Object.assign(options || {}, {
        method: method,
        body: JSON.stringify(data)
      }),
      silent
    );
  },
  patch(path, data, options, silent = false) {
    return this.post(path, data, options, "PATCH", silent);
  },
  delete(path, data, options, silent = false) {
    return this.post(path, data, options, "DELETE", silent);
  }
};
