import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Tags",
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    tags() {
      return [
        { text: "Design", value: "design" },
        { text: "Photography", value: "photography" }
      ];
    }
  },
  template: '<k-tags :value="tags" />',
});

export const list = () => ({
  computed: {
    tags() {
      return [
        { text: "Design", value: "design" },
        { text: "Photography", value: "photography" }
      ];
    }
  },
  template: '<k-tags :value="tags" layout="list" />',
});

export const nonremovable = () => ({
  computed: {
    tags() {
      return [
        { text: "Design", value: "design" },
        { text: "Photography", value: "photography" }
      ];
    }
  },
  template: '<k-tags :value="tags" :removable="false" />',
});
