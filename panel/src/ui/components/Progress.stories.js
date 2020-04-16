import Progress from "./Progress.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Interaction / Progress",
  decorators: [Padding],
  component: Progress
};

export const setValue = () => ({
  template: `
    <div>
      <k-progress ref="progress" :value="value" />

      <k-button-group>
        <k-button @click="$refs.progress.set(0)">0%</k-button>
        <k-button @click="$refs.progress.set(50)">50%</k-button>
        <k-button @click="$refs.progress.set(100)">100%</k-button>
      </k-button-group>
    </div>
  `,
});

