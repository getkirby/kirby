import ErrorBoundary from "./ErrorBoundary.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Error Handling / Error Boundary",
  component: ErrorBoundary,
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-error-boundary>
      Everything's fine. No error is thrown.
    </k-error-boundary>
  `,
});

const BadComponent = {
  created() {
    throw new Error("Something went wrong");
  }
};

export const withError = () => ({
  components: {
    "k-bad-component": BadComponent
  },
  template: `
    <k-error-boundary>
      <k-bad-component />
    </k-error-boundary>
  `
});
