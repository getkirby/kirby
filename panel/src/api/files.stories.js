import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / files",
  decorators: [Padding]
};

export const get = () => ({
  template: `
    <api-example
      call="this.$api.files.get('pages/photography+animals', 'free-wheely.jpg')"
      method="GET"
      endpoint="/api/pages/:pageId/files/:fileId"
    />
  `
});

