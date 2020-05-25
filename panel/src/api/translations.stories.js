import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / translations",
  decorators: [Padding]
};

export const list = () => ({
  template: `
    <api-example
      call="this.$api.translations.list()"
      method="GET"
      endpoint="/api/translations"
    />
  `
});

export const get = () => ({
  template: `
    <api-example
      call="this.$api.translations.get('de')"
      method="GET"
      endpoint="/api/translations/:id"
    />
  `,
});

