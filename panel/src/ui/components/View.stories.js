import View from "./View.vue";

export default {
  title: "Layout / View",
  component: View
};

export const regular = () => ({
  template: `
    <k-view>
      View content
    </k-view>
  `,
});

export const centered = () => ({
  template: `
    <k-view align="center">
      View content
    </k-view>
  `
});

