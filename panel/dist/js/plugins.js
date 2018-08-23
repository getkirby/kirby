
window.panel = window.panel || {};
window.panel.plugins = {
  components: {},
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

  // Views
  resolve(parts, "views", (name, options) => {
    window.panel.plugins["views"][name] = options;
  });
};

function resolve(object, type, callback) {
  if (object[type]) {
    Object.entries(object[type]).forEach(([name, options]) => {
      callback(name, options);
    });
  }
}
