
window.panel = window.panel || {};
window.panel.plugins = {
  components: {},
  created: [],
  fields: {},
  sections: {},
  routes: [],
  use: [],
  views: {},
};

window.panel.plugin = function (plugin, parts) {
  // Components
  resolve(parts, "components", function (name, options) {
    window.panel.plugins["components"][name] = options;
  });

  // Fields
  resolve(parts, "fields", function (name, options) {
    window.panel.plugins["fields"][`k-${name}-field`] = options;
  });

  // Sections
  resolve(parts, "sections", function (name, options) {
    window.panel.plugins["sections"][`k-${name}-section`] = options;
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
  resolve(parts, "views", function (name, options) {
    window.panel.plugins["views"][name] = options;
  });

  // Login
  if (parts.login) {
    window.panel.plugins.login = parts.login;
  }

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
