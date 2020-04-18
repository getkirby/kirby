import HeadlineField from "./HeadlineField.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Form / Field / Headline Field",
  component: HeadlineField,
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-headline-field label="Headline" />
  `
});

export const numbered = () => ({
  template: `
    <div>
      <k-headline-field
        :numbered="true"
        label="First"
      />
      <k-headline-field
        :numbered="true"
        label="Second"
      />
      <k-headline-field
        :numbered="true"
        label="Third"
      />
    </div>
  `
});

