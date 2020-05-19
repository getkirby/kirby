import { addDecorator, addParameters } from '@storybook/vue';
import Vue from "vue";

/* Ui */
import "@/ui/css/index.scss";
import Ui from "@/ui/index.js";
Vue.use(Ui);
import "@/ui/css/utilities.scss";

/* Mocks */
import Api from "./mocks/api.js";
import I18n from "./mocks/i18n.js";
import Model from "./mocks/model.js";
import Router from "./mocks/router.js";
import Store from "./mocks/store.js";

Vue.prototype.$t = I18n;
Vue.prototype.$api = Api;
Vue.prototype.$model = Model(Vue.prototype);
Vue.use(Router);
Vue.prototype.$store = Store;
Vue.prototype.$user = {
  role: { name: "admin" }
};

/* Global icons */
import Icons from "@/ui/components/Icons.vue";

addDecorator(() => {
  return {
    components: {
      "k-icons": Icons
    },
    template: `
      <div :dir="$direction">
        <k-icons />
        <div>
          <story />
        </div>
      </div>
    `
  };
});

/* Docs */
import "./theme/theme.css";

addParameters({
  docs: {
    inlineStories: true,
  },
  options: {
    storySort: (a, b) =>
      a[1].kind === b[1].kind ? 0 : a[1].id.localeCompare(b[1].id, undefined, { numeric: true }),
  },
});
