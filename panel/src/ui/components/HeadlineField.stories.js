import HeadlineField from "./HeadlineField.vue";

export default {
  title: "Form / Field / Headline Field",
  component: HeadlineField
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

