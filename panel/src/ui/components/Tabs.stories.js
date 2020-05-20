import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Tabs",
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    tabs() {
      return [
        { name: "content", label: "Content", icon: "page" },
        { name: "images", label: "Images", icon: "image" },
        { name: "downloads", label: "Downloads", icon: "download" },
        { name: "seo", label: "SEO", icon: "search" }
      ];
    },
    tab() {
      return "content";
    }
  },
  template: '<k-tabs :tabs="tabs" :tab="tab" />',
});

export const withBadges = () => ({
  computed: {
    tabs() {
      return [
        {
          name: "content",
          label: "Content",
          icon: "page",
          badge: { count: 5, color: "green" }
        },
        {
          name: "images",
          label: "Images",
          icon: "image",
          badge: 10
        },
        {
          name: "downloads",
          label: "Downloads",
          icon: "download",
          badge: { count: 7 }
        },
        {
          name: "seo",
          label: "SEO",
          icon: "search",
          badge: { count: 7, color: "aqua" }
        }
      ];
    },
    tab() {
      return "images";
    }
  },
  template: '<k-tabs :tabs="tabs" :tab="tab" />',
});
