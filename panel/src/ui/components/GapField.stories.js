import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Form / Field / Gap Field",
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        gap: {
          type: "gap",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "textarea"
        }
      };
    }
  },
  template: `
    <k-fieldset :fields="fields" />
  `,
});

export const asConditionalField = () => ({
  data() {
    return {
      values: {
        hero: "false"
      }
    }
  },
  computed: {
    fields() {
      return {
        hero: {
          label: "Has headline?",
          type: "radio",
          options: [
            { value: "true", text: "Yes" },
            { value: "false", text: "No" }
          ],
          columns: 2,
          width: "1/2"
        },
        headline: {
          label: "Headline",
          type: "text",
          width: "1/2",
          when: {
            hero: "true"
          }
        },
        gap: {
          type: "gap",
          width: "1/2",
          when: {
            hero: "false"
          }
        },
        text: {
          label: "Text",
          type: "textarea",
          width: "1/2"
        },
        category: {
          label: "Category",
          type: "select",
          options: [
            { text: "Design", value: "design" },
            { text: "Photography", value: "photography" },
            { text: "Architecture", value: "architecture" }
          ],
          width: "1/2"
        },
      };
    }
  },
  template: `
    <k-fieldset v-model="values" :fields="fields" />
  `,
});
