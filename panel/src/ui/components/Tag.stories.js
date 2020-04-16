import Tag from "./Tag.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Misc / Tag",
  component: Tag,
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

