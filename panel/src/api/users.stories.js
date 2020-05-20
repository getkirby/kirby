import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / users",
  decorators: [Padding]
};

export const list = () => ({
  template: `
    <api-example
      call="this.$api.users.list()"
      method="GET"
      endpoint="/api/users"
    />
  `
});

export const get = () => ({
  template: `
    <api-example
      call="this.$api.users.get('ada')"
      method="GET"
      endpoint="/api/users/:id"
    />
  `
});

