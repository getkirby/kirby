import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / blueprints",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.blueprints.get('pages/photography')"
      method="GET"
      endpoint="/api/blueprints/:id"
    />
  `
});

export const list = () => ({
  template: `
    <api-example
      call="this.$api.blueprints.list()"
      method="GET"
      endpoint="/api/blueprints"
    />
  `
});

