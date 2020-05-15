import FocusBoundary from "./FocusBoundary.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Focus Boundary",
  component: FocusBoundary,
  decorators: [Padding]
};

export const example = () => ({
  computed: {
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        email: {
          label: "Email",
          type: "email",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "textarea"
        }
      };
    }
  },
  template: `
    <div>
      <k-headline class="mb-3" size="large">Form 1 with focus boundary</k-headline>
      <k-focus-boundary>
        <k-fieldset :fields="fields" />
      </k-focus-boundary>

      <hr class="my-10">

      <k-headline class="mb-3" size="large">Form 2 without focus boundary</k-headline>
      <k-fieldset :fields="fields" />
    </div>
  `,
});
