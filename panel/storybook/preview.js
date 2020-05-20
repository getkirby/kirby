import { addDecorator, addParameters } from '@storybook/vue';
import Vue from "vue";

/* Ui */
import "@/ui/css/index.scss";
import Ui from "@/ui/index.js";
Vue.use(Ui);
import "@/ui/css/utilities.scss";

/* Mocks */
import Api from "../src/api/fake/index.js";
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

/** App components */
import components from "@/app/components/index.js";
Vue.use(components);

addDecorator(() => {
  return {
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

import { withA11y } from '@storybook/addon-a11y';
addDecorator(withA11y);

/* Docs */
import "./theme/theme.css";

/* Custom Components for our docs */
import ApiExample from "./components/ApiExample.vue";

Vue.component("api-example", ApiExample);

addParameters({
  options: {
    storySort: (a, b) =>
      a[1].kind === b[1].kind ? 0 : a[1].id.localeCompare(b[1].id, undefined, { numeric: true }),
  },
});
