import Tag from "./Tag.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Navigation / Tag",
  component: Tag
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

