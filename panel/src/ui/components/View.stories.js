import View from "./View.vue";

export default {
  title: "UI | Layout / View",
  component: View
};

export const regular = () => ({
  template: `
    <k-view class="py-6">
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

