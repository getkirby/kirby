import Outside from "./Outside.vue";

export default {
  title: "UI | App / Outside",
  component: Outside,
};

export const simple = () => ({
  template: `
    <k-outside>
      <k-view>
        Unauthenticated Panel view
      </k-view>
    </k-outside>
  `,
});

export const loading = () => ({
  template: `
    <k-outside :loading="true">
      <k-view>
        Unauthenticated Panel view
      </k-view>
    </k-outside>
  `,
});
