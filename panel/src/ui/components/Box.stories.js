import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / Box",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-box>Box text</k-box>
  `
});

export const positive = () => ({
  template: `
    <k-box theme="positive">A nice box</k-box>
  `
});

export const negative = () => ({
  template: `
    <k-box theme="negative">A warning box</k-box>
  `
});

export const info = () => ({
  template: `
    <k-box theme="info">A neutral info box</k-box>
  `
});

export const unstyled = () => ({
  template: `
    <k-box theme="none">An unstyled box</k-box>
  `
});

export const events = () => ({
  template: `
    <k-box text="This is a box" @click="$router.push('go/somewhere')" />
  `
});
