import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Typography / Code Block",
  decorators: [Padding]
};

export const example = () => ({
  template: `
    <k-code-block code="// this is some nice code" />
  `
});
