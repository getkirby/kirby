import SortHandle from "./SortHandle.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Misc / Sort Handle",
  component: SortHandle,
  decorators: [Padding]
};

export const regular = () => ({
  template: '<k-sort-handle />',
});

