import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / pages",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.pages.get('photography/animals')"
      method="GET"
      endpoint="/api/pages/:id"
    />
  `
});

export const blueprints = () => ({
  template: `
    <api-example
      call="this.$api.pages.blueprints('photography')"
      method="GET"
      endpoint="/api/pages/:id/children/blueprints"
    />
  `
});

