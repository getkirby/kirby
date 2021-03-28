import Vue from "vue";

/**
 * Views
 */
Object.entries(window.panel.plugins.views).forEach(([name, options]) => {
  // Check for all required properties
  if (!options.component) {
    console.error(
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

  Vue.component("k-" + name + "-plugin-view", options.component);
});

/**
 * Vue.use
 */
window.panel.plugins.use.forEach(plugin => {
  Vue.use(plugin);
});
