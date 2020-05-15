import InfoSection from "./InfoSection.vue";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Blueprints / Info Section",
  component: InfoSection,
  decorators: [Padding]
};

export const info = () => ({
  template: `
    <k-info-section
      label="Info"
      text="This is some info text"
    />
  `
});

export const positive = () => ({
  template: `
    <k-info-section
      label="Info"
      text="This is some info text"
      theme="positive"
    />
  `
});

export const negative = () => ({
  template: `
    <k-info-section
      label="Info"
      text="This is some info text"
      theme="negative"
    />
  `
});

export const noTheme = () => ({
  template: `
    <k-info-section
      label="Info"
      text="This is some info text"
      theme="none"
    />
  `
});
