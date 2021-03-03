import section from "../mixins/section/section.js";

export default {
  install(app) {

    let components = {};

    for (var key in app.options.components) {
      components[key] = app.options.components[key];
    }

    let mixins = {
      section: section
    };

    /**
     * Components
     */
    Object.entries(window.panel.plugins.components).forEach(([name, options]) => {

      // make sure component has something to show
      if (!options.template && !options.render && !options.extends) {
        app.prototype.$store.dispatch(
          "notification/error",
          `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
        );
        return;
      }

      // resolve extending via component name
      if (options.extends && typeof options.extends === "string") {
        options.extends = components[options.extends].extend({
          options,
          components: {
            ...components,
            ...options.components || {}
          }
        });

        if (options.template) {
          options.render = null;
        }
      }

      if (options.mixins) {
        options.mixins = options.mixins.map(mixin => {
          return typeof mixin === "string" ? mixins[mixin] : mixin;
        });
      }

      if (components[name]) {
        window.console.warn(`Plugin is replacing "${name}"`);
      }

      app.component(name, options);
      components[name] = app.options.components[name];
    });

    /**
     * Views
     */
    Object.entries(window.panel.plugins.views).forEach(([name, options]) => {
      // Check for all required properties
      if (!options.component) {
        app.prototype.$store.dispatch(
          "notification/error",
          `No view component provided when loading view "${name}". The view has not been registered.`
        );
        delete window.panel.plugins.views[name];
        return;
      }

      options.link = "/plugins/" + name;

      // Fallback for icon
      if (options.icon === undefined) {
        options.icon = "page";
      }

      // Fallback for menu
      if (options.menu === undefined) {
        options.menu = true;
      }

      // Update view
      window.panel.plugins.views[name] = {
        id: name,
        text: options.text || options.label,
        link: options.link,
        icon: options.icon,
        menu: options.menu
      };

      app.component("k-" + name + "-plugin-view", options.component);
    });

    /**
     * Vue.use
     */
    window.panel.plugins.use.forEach(plugin => app.use(plugin));
  }
}


