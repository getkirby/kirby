export default {
  title: "App | Separation / Outside"
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
