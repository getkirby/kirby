import { addDecorator, addParameters } from '@storybook/vue';
import Icons from "@/ui/components/Icons.vue";
import Vue from "vue";
import Ui from "@/ui/index.js";
import UiMocks from "@/ui/plugins/mocks.js";

Vue.use(UiMocks);
Vue.use(Ui);

import "@/ui/css/index.scss";
import "./theme.css";
import "@/ui/index.js";

addParameters({
  docs: {
    inlineStories: true,
  },
});

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
