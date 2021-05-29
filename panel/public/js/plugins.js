
window.panel = window.panel || {};
window.panel.plugins = {
  components: {},
  created: [],
  icons: {},
  routes: [],
  use: [],
  views: {},
  thirdParty: {}
};

window.panel.plugin = function (plugin, parts) {
  // Blocks
  resolve(parts, "blocks", function (name, options) {
    if (typeof options === "string") {
      options = { template: options };
    }

    window.panel.plugins["components"][`k-block-type-${name}`] = {
      extends: "k-block-type",
      ...options
    };
  });

  // Components
  resolve(parts, "components", function (name, options) {
    window.panel.plugins["components"][name] = options;
  });

  // Fields
  resolve(parts, "fields", function (name, options) {
    window.panel.plugins["components"][`k-${name}-field`] = options;
  });

  // Icons
  resolve(parts, "icons", function (name, options) {
    window.panel.plugins["icons"][name] = options;
  });

  // Sections
  resolve(parts, "sections", function (name, options) {
    window.panel.plugins["components"][`k-${name}-section`] = {
      ...options,
      mixins: ["section"].concat(options.mixins || [])
    };
  });

  // Vue.use
  resolve(parts, "use", function (name, options) {
    window.panel.plugins["use"].push(options);
  });

  // created callback
  if (parts["created"]) {
    window.panel.plugins["created"].push(parts["created"]);
  }

  // Views
  // @todo Remove in 3.7.0
  resolve(parts, "views", function (name, options) {
    window.panel.plugins["views"][name] = options;
  });

  // Login
  if (parts.login) {
    window.panel.plugins.login = parts.login;
  }

  // Third-party plugins
  // @todo is this ever used anywhere?
  resolve(parts, "thirdParty", function(name, options) {
    window.panel.plugins["thirdParty"][name] = options;
  });

};

function resolve(object, type, callback) {
  if (object[type]) {

    if (Object.entries) {
      Object.entries(object[type]).forEach(function ([name, options]) {
        callback(name, options);
      });
    }

  }
}
