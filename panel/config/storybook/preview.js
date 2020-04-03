import { addDecorator } from '@storybook/vue';
import Icons from "@/ui/components/Icons.vue";
import Vue from "vue";
import Ui from "@/ui/index.js";

Vue.use(Ui);

/** Store mockup */
Vue.prototype.$store = {
  dispatch() {

  },
  commit() {

  }
};

import "@/ui/css/index.scss";
import "@/ui/index.js";

addDecorator(() => {
  return {
    components: {
      "k-icons": Icons
    },
    template: `
      <div dir="ltr" style="padding: 1.5rem">
        <k-icons />
        <div>
          <story />
        </div>
      </div>
    `
  }
});
