import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Data / Empty Items",
  decorators: [Padding]
};

export const list = () => ({
  template: `
    <k-empty-items />
  `
});

export const listWithInfo = () => ({
  template: `
    <k-empty-items :info="true" />
  `
});

export const cardlets = () => ({
  template: `
    <k-empty-items layout="cardlet" />
  `
});

export const cardletsWithInfo = () => ({
  template: `
    <k-empty-items layout="cardlet" :info="true" />
  `
});

export const cards = () => ({
  template: `
    <k-empty-items layout="card" />
  `
});

export const cardsWithInfo = () => ({
  template: `
    <k-empty-items layout="card" :info="true" />
  `
});

export const cardsWithCustomRatio = () => ({
  template: `
    <k-empty-items
      layout="card"
      ratio="3/2"
    />
  `
});
