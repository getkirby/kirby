import LineField from "./LineField.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Form / Field / Line Field",
  component: LineField,
  decorators: [Padding]
};

export const regular = () => ({
  template: '<k-line-field />',
});
