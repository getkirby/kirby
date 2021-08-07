
import string from "@/helpers/string.js";

class Components {

  constructor(app) {
    this.app = app;
    this.mixins = {};
    this.components = {};
    this.queue = {};

    // Load mixins
    const require = import.meta.globEager('../mixins/*.js');

    Object.keys(require).map((key) => {
      const name = key.match(/\/([a-zA-Z]*)\.js/)[1];
      this.mixins[name] = require[key].default;
    });

    // Register native and plugin components
    this.fromCore();
    this.fromPlugins();
    this.resolveQueue();
  }

  component(name, component) {
    // If component should be extended (resolve component name string)
    if (component.extends && typeof component.extends === "string") {
      const parent = this.components[component.extends];

      // If the parent component has not been registered itself yet,
      // delay registration
      if (!parent) {
        this.queue[name] = component;
        return;
      }

      // Resolve extends string to parent component
      component.extends = parent.extend({
        component,
        components: {
          ...this.components,
          ...component.components || {}
        }
      });

      // Clear render function if component template is set
      if (component.template) {
        component.render = null;
      }
    }

    // Resolve mixins if they are defined as string alias
    if (component.mixins) {
      component.mixins = component.mixins.map(mixin => typeof mixin === "string" ? this.mixins[mixin] : mixin);
    }

    // Register component globally and
    // delete from queue (if it was on it before)
    this.app.component(name, component);
    this.components[name] = this.app.options.components[name];
    delete this.queue[name];
  }

  fromCore() {
    const require = import.meta.globEager('./**/*.vue');

    Object.keys(require).map((key) => {
      const file = key.match(/\/([a-zA-Z]*)\.vue/)[1];
      const name = "k-" + string.camelToKebab(file);

      // Load the component
      let component = require[key].default;

      // Try to register component
      this.component(name, component);
    });
  }

  fromPlugins() {
    Object.entries(window.panel.plugins.components).forEach(([name, component]) => {

      // Make sure component has something to show
      if (!component.template && !component.render && !component.extends) {
        console.error(
          `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
          );
        return;
      }

      // Try to register component
      this.component(name, component);
    });

    /**
     * Vue.use
     */
    window.panel.plugins.use.forEach(plugin => {
      this.app.use(plugin);
    });
  }

  resolveQueue() {
    let current  = Object.keys(this.queue).length;
    let previous = current + 1;

    while (current < previous && current > 0) {
      Object.entries(this.queue).forEach(([name, component]) => {
        this.component(name, component);
      });
      previous = current;
      current  = Object.keys(this.queue).length;
    }

    if (current > 0) {
      console.warn("Some component dependencies could not be resolved")
      console.error(this.queue);
    }
  }

}

export default {
  install(app) {
    new Components(app);
  }
}