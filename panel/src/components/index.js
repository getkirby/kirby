
import string from "@/helpers/string.js";

const reqq = require.context("../mixins/", true, /\.js/i);
const mixins = {};
reqq.keys().forEach(key => {
  const name = key.match(/\.\/([a-zA-Z/]*)\.js/)[1];
  mixins[name] = reqq(key).default;
});

export default {
  components: {},
  queue: {},
  install(app) {
    this.app = app;
    this.loadCore();
    this.loadPlugins();
    this.resolveQueue();
  },
  register(name, component) {
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
      component.mixins = component.mixins.map(mixin => {
        return typeof mixin === "string" ? mixins[mixin] : mixin;
      });
    }
   
    // Register component globally and 
    // delete from queue (if it was on it before)
    this.app.component(name, component);
    this.components[name] = this.app.options.components[name];
    delete this.queue[name];
  },
  loadCore() {
    const req = require.context("./", true, /\.vue$/i);

    req.keys().map((key) => {
      // Get name and type by filename
      const file = key.match(/\/([a-zA-Z]*)\.vue/)[1];
      const name = "k-" + string.camelToKebab(file);

      // Load the component
      let component = req(key).default;
    
      // Try to register component
      this.register(name, component);
    });
  },
  loadPlugins() {
    Object.entries(window.panel.plugins.components).forEach(([name, component]) => {

      // Make sure component has something to show
      if (!component.template && !component.render && !component.extends) {
        console.error(
          `Neither template or render method provided nor extending a component when loading plugin component "${name}". The component has not been registered.`
        );
        return;
      }

      // Try to register component
      this.register(name, component);
    });
  },
  resolveQueue() {
    let current  = Object.keys(this.queue).length;
    let previous = current + 1;

    while (current < previous && current > 0) {
      Object.entries(this.queue).forEach(([name, component]) => {
        this.register(name, component);
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