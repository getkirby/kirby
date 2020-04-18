import Text from "./Text.vue";
import Padding from "../storybook/Padding.js";
import { withKnobs, text, select } from '@storybook/addon-knobs';

export default {
  title: "UI | Typography / Text",
  decorators: [withKnobs, Padding],
  component: Text
};

export const playground = () => ({
  data() {
    return {
      settings: {
        align: "left",
        size: "regular",
        theme: "none",
        text: "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet."
      }
    };
  },
  computed: {
    fields() {
      return {
        align: {
          label: "Align",
          type: "select",
          width: "1/3",
          empty: false,
          options: [
            { value: "left", text: "left" },
            { value: "center", text: "center" },
            { value: "right", text: "right" }
          ]
        },
        size: {
          label: "Size",
          type: "select",
          width: "1/3",
          empty: false,
          options: [
            { value: "tiny", text: "tiny" },
            { value: "small", text: "small" },
            { value: "regular", text: "regular" },
            { value: "large", text: "large" }
          ]
        },
        theme: {
          label: "Theme",
          type: "select",
          width: "1/3",
          empty: false,
          options: [
            { value: "none", text: "none" },
            { value: "help", text: "help" },
          ]
        },
        text: {
          label: "Text",
          type: "textarea",
          buttons: false
        },
      };
    }
  },
  template: `
    <k-grid gutter="large">
      <k-column width="1/2">
        <k-fieldset
          :fields="fields"
          v-model="settings"
        />
      </k-column>
      <k-column width="1/2">
        <k-headline class="mb-3">Result:</k-headline>
        <k-text
          :align="settings.align"
          :size="settings.size"
          :theme="settings.theme"
          class="bg-white p-6"
          v-html="settings.text"
        />
      </k-column>
    </k-grid>
  `,
});
