import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / languages",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.languages.get('de')"
      method="GET"
      endpoint="/api/languages/:id"
    />
  `
});

export const list = () => ({
  template: `
    <api-example
      call="this.$api.languages.list()"
      method="GET"
      endpoint="/api/languages"
    />
  `
});

