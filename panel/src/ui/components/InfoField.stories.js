import InfoField from "./InfoField.vue";

export default {
  title: "Form / Field / Info Field",
  component: InfoField
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
