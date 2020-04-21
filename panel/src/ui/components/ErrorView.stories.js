import ErrorView from "./ErrorView.vue";

export default {
  title: "UI | Error Handling / ErrorView",
  component: ErrorView,
};

export const regular = () => ({
  template: `
    <k-error-view>
      Something went wrong
    </k-error-view>
  `,
});

export const longText = () => ({
  template: `
    <k-error-view>
      Something went wrong and everything is
      fucked up and broken and cannot be recovered.
      We are really sorry, but there's nothing you can do.
    </k-error-view>
  `,
});

