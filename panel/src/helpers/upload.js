export default (file, params) => {
  const defaults = {
    url: "/",
    field: "file",
    method: "POST",
    attributes: {},
    complete: function () {},
    error: function () {},
    success: function () {},
    progress: function () {}
  };

  const options = Object.assign(defaults, params);
  const formData = new FormData();

  formData.append(options.field, file, file.name);

  if (options.attributes) {
    Object.keys(options.attributes).forEach((key) => {
      formData.append(key, options.attributes[key]);
    });
  }

  const xhr = new XMLHttpRequest();

  const progress = (event) => {
    if (!event.lengthComputable || !options.progress) {
      return;
    }

    let percent = Math.max(
      0,
      Math.min(100, (event.loaded / event.total) * 100)
    );

    options.progress(xhr, file, Math.ceil(percent));
  };

  xhr.upload.addEventListener("loadstart", progress);
  xhr.upload.addEventListener("progress", progress);

  xhr.addEventListener("load", (event) => {
    let json = null;

    try {
      json = JSON.parse(event.target.response);
    } catch (e) {
      json = { status: "error", message: "The file could not be uploaded" };
    }

    if (json.status === "error") {
      options.error(xhr, file, json);
    } else {
      options.success(xhr, file, json);
      options.progress(xhr, file, 100);
    }
  });

  xhr.addEventListener("error", (event) => {
    const json = JSON.parse(event.target.response);

    options.error(xhr, file, json);
    options.progress(xhr, file, 100);
  });

  xhr.open(options.method, options.url, true);

  // add all request headers
  if (options.headers) {
    Object.keys(options.headers).forEach((header) => {
      const value = options.headers[header];
      xhr.setRequestHeader(header, value);
    });
  }

  xhr.send(formData);
};
