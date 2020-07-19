import section from "@/mixins/section/section.js";

export default {
  install(Vue, store) {

    const panel = window.panel || {};

    Vue.prototype.$plugins = Vue.$plugins = {
      components: {},
      created: [],
      fields: {},
      icons: {},
      sections: {},
      routes: [],
      use: [],
      views: {},
      thirdParty: {},
      ...(panel.plugins || {}),
    };

    let components = {};

    for (var key in Vue.options.components) {
      components[key] = Vue.options.components[key];
    }

    const registerComponent = (name, component) => {
      if (!component.template && !component.render && !component.extends) {
        store.dispatch(
          "notification/error",
          `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
        );
        return;
      }

      if (component.extends && typeof component.extends === "string") {
        component.extends = components[component.extends];
        if (component.template) {
          component.render = null;
        }
      }

      if (component.mixins) {
        component.mixins = component.mixins.map(mixin => {
          return typeof mixin === "string" ? components[mixin] : mixin;
        });
      }

      if (components[name]) {
        window.console.warn(`Plugin is replacing "${name}"`);
      }

      Vue.component(name, component);
    };

    /**
     * Components
     */
    Object.entries(Vue.$plugins.components).forEach(([name, options]) => {
      registerComponent(name, options);
    });

    /**
     * Fields
     */
    Object.entries(Vue.$plugins.fields).forEach(([name, options]) => {
      registerComponent(name, options);
    });

    /**
     * Sections
     */
    Object.entries(Vue.$plugins.sections).forEach(([name, options]) => {
      registerComponent(name, {
        ...options,
        mixins: [section].concat(options.mixins || [])
      });
    });

    /**
     * Views
     */
    Object.entries(Vue.$plugins.views).forEach(([name, options]) => {
      // Check for all required properties
      if (!options.component) {
        store.dispatch(
          "notification/error",
          `No view component provided when loading view "${name}". The view has not been registered.`
        );
        delete Vue.$plugins.views[name];
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
      Vue.$plugins.views[name] = {
        id: name,
        text: options.text || options.label,
        link: options.link,
        icon: options.icon,
        menu: options.menu
      };

      Vue.component("k-" + name + "-plugin-view", options.component);
    });

    /**
     * Vue.use
     */
    Vue.$plugins.use.forEach(plugin => {
      Vue.use(plugin);
    });

  }
};
