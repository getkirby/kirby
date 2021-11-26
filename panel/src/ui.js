import Vue from "vue";

import Api from "./config/api.js";
import Errors from "./config/errors.js";
import Events from "./config/events.js";
import Fiber from "./fiber/plugin.js";
import Helpers from "./helpers/index.js";
import I18n from "./config/i18n.js";
import Libraries from "./config/libraries.js";
import Plugins from "./config/plugins.js";
import store from "./store/store.js";

import Portal from "@linusborg/vue-simple-portal";
import Vuelidate from "vuelidate";

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(Errors);
Vue.use(Helpers);
Vue.use(Libraries);

import "./config/components.js";

Vue.use(Plugins);
Vue.use(Events);
Vue.use(I18n);
Vue.use(Fiber);
Vue.use(Api, store);

Vue.use(Portal);
Vue.use(Vuelidate);
