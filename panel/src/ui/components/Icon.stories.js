import Icon from "./Icon.vue";
import Padding from "../storybook/Padding.js";
import { withKnobs, text, color, select } from '@storybook/addon-knobs';

export default {
  title: "Media / Icon",
  decorators: [withKnobs, Padding],
  component: Icon
};

export const configurator = () => ({
  template: `<k-icon style="width: 6rem; height: 6rem" v-bind="$props">`,
  props: {
    back: {
      default: select("back", ["", "black", "white", "pattern"], "")
    },
    color: {
      default: color("color", "black")
    },
    size: {
      default: select("size", ["regular", "medium", "large"], "normal")
    },
    type: {
      default: text("type", "edit")
    }
  }
});

