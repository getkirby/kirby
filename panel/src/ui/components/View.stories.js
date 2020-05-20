import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / View",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-view>
      View content
    </k-view>
  `
});

export const centred = () => ({
  template: `
    <k-view align="center">
      View content
    </k-view>
  `
});
