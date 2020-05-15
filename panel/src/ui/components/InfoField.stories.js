import InfoField from "./InfoField.vue";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Form / Field / Info Field",
  component: InfoField,
  decorators: [Padding]
};

export const info = () => ({
  template: `
    <k-info-field
      label="Info"
      text="This is some info text"
    />
  `
});

export const positive = () => ({
  template: `
    <k-info-field
      label="Info"
      text="This is some info text"
      theme="positive"
    />
  `
});

export const negative = () => ({
  template: `
    <k-info-field
      label="Info"
      text="This is some info text"
      theme="negative"
    />
  `
});

export const noTheme = () => ({
  template: `
    <k-info-field
      label="Info"
      text="This is some info text"
      theme="none"
    />
  `
});
