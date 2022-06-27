import store from "@/store/store.js";
import section from "../mixins/section.js";

export default {
  install(app) {
    const components = { ...app.options.components };

    const mixins = {
      section: section
    };

    /**
     * Components
     */
    for (const [name, options] of Object.entries(
      window.panel.plugins.components
    )) {
      // make sure component has something to show
      if (!options.template && !options.render && !options.extends) {
        store.dispatch(
          "notification/error",
          `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
        );
        continue;
      }

      // resolve extending via component name
      if (typeof options?.extends === "string") {
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
      }

      if (options.template) {
        options.render = null;
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

    /**
     * `Vue.use`
     */
    for (const plugin of window.panel.plugins.use) {
      app.use(plugin);
    }
  }
};
