
window.panel = window.panel || {};
window.panel.plugins = {
  components: {},
  fields: {},
  routes: [],
  translations: {},
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
    window.panel.plugins["fields"][`kirby-${name}-field`] = options;
  });

  // Sections
  resolve(parts, "sections", function (name, options) {
    window.panel.plugins["components"][`kirby-${name}-section`] = options;
  });

  // Translations
  resolve(parts, "translations", function (locale, strings) {
    window.panel.plugins["translations"][locale] = Object.assign(
      window.panel.plugins["translations"][locale],
      strings
    );
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
