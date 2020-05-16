import Backdrop from "./Backdrop.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Layout / Backdrop",
  decorators: [Padding],
  component: Backdrop
};

export const simple = () => ({
  methods: {
    onClick: action("click")
  },
  template: `
    <div>
      Background
      <k-backdrop @click="onClick">
        <p class="text-white p-6">Backdrop content</p>
      </k-backdrop>
    </div>
  `
});
