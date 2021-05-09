import { createApp, h } from 'vue'
import App from "./App.vue";
import Api from "./config/api.js";
import Components from "./config/components.js";
import Errors from "./config/errors.js";
import Events from "./config/events.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Libraries from "./config/libraries.js";
import Plugins from "./config/plugins.js";
import Router from "./config/router.js";
import Store from "./store/index.js";
// import VuePortal from "@linusborg/vue-simple-portal";

const app = createApp({
  created() {
    window.panel.app = this;

    // created plugin callbacks
    window.panel.plugins.created.forEach(plugin => plugin(this));

    // initialize content store
    this.$store.dispatch("content/init");
  },
  render: () => h(App)
});

app.use(Helpers);
app.use(Libraries);
app.use(Events);
app.use(Errors);
app.use(Components);
app.use(Api);
app.use(Store);
app.use(I18n);
app.use(Router);
app.use(Plugins);
// TODO: replace with native
//app.use(VuePortal);


app.mount('#app')
