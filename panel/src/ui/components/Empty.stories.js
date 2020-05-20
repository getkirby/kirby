import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / Empty",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-empty>
      This is an empty state
    </k-empty>
  `
});

export const icon = () => ({
  template: `
    <k-empty icon="page">
      This is an empty state
    </k-empty>
  `
});

export const cardLayout = () => ({
  template: `
    <k-empty icon="page" layout="cards">
      This is an empty state
    </k-empty>
  `
});
