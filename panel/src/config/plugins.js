import Vue from "vue";
import store from "@/store/store.js";
import section from "../mixins/section/section.js";

let components = {};

for (var key in Vue.options.components) {
  components[key] = Vue.options.components[key];
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
    store.dispatch(
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

  Vue.component(name, options);
  components[name] = Vue.options.components[name];
});

/**
 * Vue.use
 */
window.panel.plugins.use.forEach(plugin => {
  Vue.use(plugin);
});
