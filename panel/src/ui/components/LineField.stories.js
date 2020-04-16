import LineField from "./LineField.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Form / Field / Line Field",
  component: LineField,
  decorators: [Padding]
};

export const regular = () => ({
  template: '<k-line-field />',
});
