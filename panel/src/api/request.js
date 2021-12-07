export async function toJson(response) {
  const text = await response.text();
  let data;

  try {
    data = JSON.parse(text);
  } catch (e) {
    window.panel.$vue.$api.onParserError({ html: text });
    return false;
  }

  return data;
}

export default (config) => {
  return {
    running: 0,
    async request(path, options, silent = false) {
      // create options object
      options = Object.assign(options || {}, {
        credentials: "same-origin",
        cache: "no-store",
        headers: {
          "x-requested-with": "xmlhttprequest",
          "content-type": "application/json",
          ...options.headers
        }
      });

      // adapt headers for all non-GET and non-POST methods
      if (
        config.methodOverwrite &&
        options.method !== "GET" &&
        options.method !== "POST"
      ) {
        options.headers["x-http-method-override"] = options.method;
        options.method = "POST";
      }

      // CMS specific options via callback
      options = config.onPrepare(options);

      // create a request id
      const id = path + "/" + JSON.stringify(options);

      config.onStart(id, silent);
      this.running++;

      // fetch the resquest's response
      const response = await fetch(
        [config.endpoint, path].join(
          config.endpoint.endsWith("/") || path.startsWith("/") ? "" : "/"
        ),
        options
      );

      try {
        // try to parse JSON
        const json = await toJson(response);

        // check for the server response code
        if (response.status < 200 || response.status > 299) {
          throw json;
        }

        // look for an error status
        if (json.status === "error") {
          throw json;
        }

        let data = json;

        if (json.data && json.type === "model") {
          data = json.data;
        }

        this.running--;
        config.onComplete(id);
        config.onSuccess(json);
        return data;
      } catch (e) {
        this.running--;
        config.onComplete(id);
        config.onError(e);
        throw e;
      }
    },
    async get(path, query, options, silent = false) {
      if (query) {
        path +=
          "?" +
          Object.keys(query)
            .filter((key) => query[key] !== undefined && query[key] !== null)
            .map((key) => key + "=" + query[key])
            .join("&");
      }

      return this.request(
        path,
        Object.assign(options || {}, {
          method: "GET"
        }),
        silent
      );
    },
    async post(path, data, options, method = "POST", silent = false) {
      return this.request(
        path,
        Object.assign(options || {}, {
          method: method,
          body: JSON.stringify(data)
        }),
        silent
      );
    },
    async patch(path, data, options, silent = false) {
      return this.post(path, data, options, "PATCH", silent);
    },
    async delete(path, data, options, silent = false) {
      return this.post(path, data, options, "DELETE", silent);
    }
  };
};
