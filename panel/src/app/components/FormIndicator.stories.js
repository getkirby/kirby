import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Form / Form Indicator",
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    models() {
      return {
        "pages/photography": {
          changes: {
            text: "Lorem Ipsum"
          },
          api: "pages/photography"
        },
        "files/photography+animals/free-wheely.jpg": {
          changes: {
            caption: "This is a caption!"
          },
          api: "pages/photography+animals/files/free-wheely.jpg"
        },
        "users/ada": {
          changes: {
            twitter: "@ada"
          },
          api: "users/ada"
        }
      }
    }
  },
  template: `
    <k-topbar>
      <template v-slot:options>
        <k-form-indicator :models="models" />
      </template>
    </k-topbar>
  `
});

export const noChanged = () => ({
  extends: regular(),
  computed: {
    models() {
      return {};
    }
  },
});
