import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Breadcrumb",
  decorators: [Padding]
};

export const simple = () => ({
  computed: {
    links() {
      return [
        { link: "https://getkirby.com", text: "Home" },
        { link: "https://getkirby.com/docs", text: "Docs" },
        { link: "https://getkirby.com/docs/guide", text: "Guide" },
        { link: "https://getkirby.com/docs/guide/blueprints", text: "Blueprints" }
      ];
    }
  },
  template: `
    <k-breadcrumb :links="links" />
  `,
});

export const withIcon = () => ({
  computed: {
    links() {
      return [
        { link: "https://getkirby.com", text: "Home", icon: "home" },
        { link: "https://getkirby.com/docs", text: "Docs" },
        { link: "https://getkirby.com/docs/guide", text: "Guide" },
        { link: "https://getkirby.com/docs/guide/blueprints", text: "Blueprints" }
      ];
    }
  },
  template: `
    <k-breadcrumb :links="links" />
  `,
});

export const loading = () => ({
  computed: {
    links() {
      return [
        { link: "https://getkirby.com", text: "Home", loading: true },
        { link: "https://getkirby.com/docs", text: "Docs" },
        { link: "https://getkirby.com/docs/guide", text: "Guide" },
        { link: "https://getkirby.com/docs/guide/blueprints", text: "Blueprints" }
      ];
    }
  },
  template: `
    <k-breadcrumb :links="links" />
  `,
});
