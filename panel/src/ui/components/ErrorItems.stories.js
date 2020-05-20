import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Error Handling / Error Items",
  decorators: [Padding]
};

export const list = () => ({
  template: `
    <k-error-items>
      This is an error! <k-link to="reload" class="ml-2 underline">Try again</k-link>
    </k-error-items>
  `
});

export const cardlets = () => ({
  template: `
    <k-error-items layout="cardlet">This is an error!</k-error-items>
  `
});

export const cards = () => ({
  template: `
    <k-error-items layout="card">This is an error!</k-error-items>
  `
});
