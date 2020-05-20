import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Tag",
  decorators: [Padding]
};

export const regular = () => ({
  template: '<k-tag>Tag</k-tag>',
});

export const removable = () => ({
  methods: {
    remove: action("remove"),
  },
  template: '<k-tag :removable="true" @remove="remove">Tag</k-tag>'
});
