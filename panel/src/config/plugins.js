import store from "@/store/store.js";
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
    Object.entries(window.panel.plugins.components).forEach(
      ([name, options]) => {
        // make sure component has something to show
        if (!options.template && !options.render && !options.extends) {
          store.dispatch(
            "notification/error",
            `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
          );
          return;
        }

        // resolve extending via component name
        if (options.extends && typeof options.extends === "string") {
          // only extend if referenced component exists
          if (components[options.extends]) {
            options.extends = components[options.extends].extend({
              options,
              components: {
                ...components,
                ...(options.components || {})
              }
            });
          } else {
            // if component doesn't exist, don't extend
            options.extends = null;
          }

          if (options.template) {
            options.render = null;
          }
        }

        if (options.mixins) {
          options.mixins = options.mixins.map((mixin) => {
            return typeof mixin === "string" ? mixins[mixin] : mixin;
          });
        }

        if (components[name]) {
          window.console.warn(`Plugin is replacing "${name}"`);
        }

        app.component(name, options);
        components[name] = app.options.components[name];
      }
    );

    /**
     * app.use
     */
    window.panel.plugins.use.forEach((plugin) => {
      app.use(plugin);
    });
  }
};
