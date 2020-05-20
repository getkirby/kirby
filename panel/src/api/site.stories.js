import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / site",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.site.get()"
      method="GET"
      endpoint="/api/site"
    />
  `
});

