import Vue from "vue";
import store from "@/store/store.js";

let components = {};

for (var key in Vue.options.components) {
  components[key] = Vue.options.components[key];
}

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

  if (components[name]) {
    window.console.warn(`Plugin is replacing "${name}"`);
  }

  Vue.component(name, options);
  components[name] = Vue.options.components[name];
});

/**
 * Views
 */
Object.entries(window.panel.plugins.views).forEach(([name, options]) => {
  // Check for all required properties
  if (!options.component) {
    store.dispatch(
      "notification/error",
      `No view component provided when loading view "${name}". The view has not been registered.`
    );
    delete window.panel.plugins.views[name];
    return;
  }


  // Fallbacks
  if (options.icon === undefined) {
    options.icon = "page";
  }

  if (options.menu === undefined) {
    options.menu = true;
  }

  if (options.search === undefined) {
    options.menu = "pages";
  }

  // Update view
  window.panel.plugins.views[name] = {
    id: name,
    label: options.text || options.label,
    icon: options.icon,
    link: "/plugins/" + name,
    menu: options.menu,
  };

  Vue.component("k-" + name + "-plugin-view", options.component);
});

/**
 * Vue.use
 */
window.panel.plugins.use.forEach(plugin => {
  Vue.use(plugin);
});
