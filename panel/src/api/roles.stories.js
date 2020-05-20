import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / roles",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.roles.get('admin')"
      method="GET"
      endpoint="/api/roles/:id"
    />
  `
});

export const list = () => ({
  template: `
    <api-example
      call="this.$api.roles.list()"
      method="GET"
      endpoint="/api/roles"
    />
  `
});

