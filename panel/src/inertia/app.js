import Vue from 'vue'
import Inertia from './inertia.js'

export default {
  name: 'Inertia',
  data() {
    return {
      component: null,
      page: window.inertia,
      key: null,
    }
  },
  created() {
    Inertia.init({
      page: window.inertia,
      component: (name) => Vue.component(name),
      props: (props) => {

        /** Set translation */
        document.querySelector("html").setAttribute("lang", props.$translation.code);

        /** Set globals */
        Vue.prototype.$config      = window.panel.$config      = props.$config;
        Vue.prototype.$language    = window.panel.$language    = props.$language;
        Vue.prototype.$languages   = window.panel.$languages   = props.$languages;
        Vue.prototype.$permissions = window.panel.$permissions = props.$permissions;
        Vue.prototype.$system      = window.panel.$system      = props.$system;
        Vue.prototype.$translation = window.panel.$translation = props.$translation;
        Vue.prototype.$urls        = window.panel.$urls        = props.$urls;
        Vue.prototype.$user        = window.panel.$user        = props.$user;
        Vue.prototype.$view        = window.panel.$view        = props.$view;
        Vue.prototype.$views       = window.panel.$views       = {
          ...props.$views,
          ...window.panel.plugins.views
        };

        return props.$props;
      },
      swap: async ({ component, page, preserveState }) => {
        this.component = component
        this.page = page
        this.key = preserveState ? this.key : Date.now()
      },
    })
  },
  render(h) {
    if (this.component) {
      return h(this.component, {
        key: this.key,
        props: this.page.props,
        scopedSlots: this.$scopedSlots,
      })
    }
  }
}

export const plugin = {
  install(Vue) {
    Vue.prototype.$url    = (path = "") => document.querySelector("base").href + path.replace(/^\//, "");
    Vue.prototype.$go     = (path, options) => Inertia.visit(Vue.prototype.$url(path), options)
    Vue.prototype.$reload = (options)   =>  Inertia.reload(options);
  },
}
