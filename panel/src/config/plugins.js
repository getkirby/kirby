import Vue from "vue";
import auth from "./auth.js";
import store from "./store.js";
import section from "../mixins/section.js";
import { ucfirst, lcfirst } from "@/ui/helpers/stringCase.js";

let components = {};

for (var key in Vue.options.components) {
  components[key] = Vue.options.components[key]
}

const registerComponent = (name, component) => {
  if (!component.template && !component.render && !component.extends) {
    store.dispatch("notification/error", `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`);
    return;
  }

  if (component.extends && typeof component.extends === 'string') {
    component.extends = components[component.extends];
    if (component.template) {
      component.render = null;
    }
  }

  if (component.mixins) {
    component.mixins = component.mixins.map(mixin => {
      return typeof mixin === 'string' ? components[mixin] : mixin;
    });
  }

  if (components[name]) {
    window.console.warn(`Plugin is replacing "${name}"`);
  }

  Vue.component(name, component);
};

// Components
Object.entries(window.panel.plugins.components).forEach(([name, options]) => {
  registerComponent(name, options);
});

Object.entries(window.panel.plugins.fields).forEach(([name, options]) => {
  registerComponent(name, options);
});

Object.entries(window.panel.plugins.sections).forEach(([name, options]) => {
  registerComponent(name, {
    ...section,
    ...options
  });
});


// Views
Object.entries(window.panel.plugins.views).forEach(([name, options]) => {
  // Check for all required properties
  if (!options.component) {
    store.dispatch("notification/error", `No view component provided when loading view "${name}". The view has not been registered.`);
    delete window.panel.plugins.views[name];
    return;
  }

  // Fallback for link
  if (!options.link) {
    options.link = "/plugin/" + lcfirst(name);
  }

  // Fallback for icon
  if (!options.icon) {
    options.icon = "page";
  }

  // Fallback for menu
  if (!options.menu) {
    options.menu = true;
  }

  // Route
  if (!options.route) {
    // Fallback for route
    options.route = {
      name: name,
      path: options.link,
      beforeEnter: auth
    };
  } else {
    // Fallback for route name
    if (!options.route.name) {
      options.route.name = ucfirst(name);
    }

    // Fallback for route path
    if (!options.route.path) {
      options.route.name = options.link;
    }

    // Fallback for route meta.view
    if (!options.route.meta) {
      options.route.meta = {
        view: name
      };
    } else if (!options.route.meta.view) {
      options.route.meta.view = name;
    }

    // inject auth route guard
    if (!options.route.beforeEnter) {
      options.route.beforeEnter = auth;
    }
  }

  // Register route
  window.panel.plugins.routes.push({
    ...options.route,
    component: options.component
  });

  // Update view
  window.panel.plugins.views[name] = {
    link: options.link,
    icon: options.icon,
    menu: options.menu
  };
});

// Vue.use
window.panel.plugins.use.forEach(plugin => {
  Vue.use(plugin);
});
