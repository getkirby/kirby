import Breadcrumb from "./Breadcrumb.vue";

export default {
  title: "Navigation / Breadcrumb",
  component: Breadcrumb,
};

export const regular = () => ({
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

