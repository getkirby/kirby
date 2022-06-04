/**
 * @param {File} file
 * @param {object} params
 */
export default (file, params) => {
  const defaults = {
    url: "/",
    field: "file",
    method: "POST",
    size: 2048,
    attributes: {},
    complete: function () {},
    error: function () {},
    success: function () {},
    progress: function () {}
  };

  const options = Object.assign(defaults, params);

  // number of chunks
  const total = Math.ceil(file.size / options.size);

  // accumulated uploaded size tracker
  let progress = 0;

  // set up shared form data for all chunks
  const formData = new FormData();

  if (options.attributes) {
    for (const key in options.attributes) {
      formData.append(key, options.attributes[key]);
    }
  }

  /**
   * Calculate total progess for all chunks
   * @param {ProgressEvent} event
   */
  const setProgress = (event) => {
    if (!event.lengthComputable || !options.progress) {
      return;
    }

    progress += event.loaded;
    let percent = Math.max(0, Math.min(100, (progress / file.size) * 100));

    options.progress(file, Math.ceil(percent));
  };

  /**
   * Sends each chunk
   * @param {Blob} chunk
   * @param {number} index
   * @param {FormData} data
   * @param {object} options
   */
  const send = (chunk, index, data, options) => {
    data.append(options.field, chunk, `${file.name}.part`);
    data.append("last", index >= total);

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener("loadstart", setProgress);
    xhr.upload.addEventListener("progress", setProgress);

    xhr.addEventListener("load", (event) => {
      let json = null;

      try {
        json = JSON.parse(event.target.response);
      } catch (e) {
        json = { status: "error", message: "The file could not be uploaded" };
      }

      if (json.status === "error") {
        options.error(file, json);
      } else {
        options.success(file, json);
        options.progress(file, 100);
      }
    });

    xhr.addEventListener("error", (event) => {
      const json = JSON.parse(event.target.response);

      options.error(file, json);
      options.progress(file, 100);
    });

    xhr.open(options.method, options.url, true);

    // add all request headers
    if (options.headers) {
      Object.keys(options.headers).forEach((header) => {
        const value = options.headers[header];
        xhr.setRequestHeader(header, value);
      });
    }

    xhr.send(data);
  };

  // split into chunks and send
  for (let i = 0; i < total; i++) {
    const chunk = file.slice(
      i * options.size,
      Math.min(i * options.size + options.size, file.size),
      file.type
    );

    send(chunk, i, formData, options);
  }
};
