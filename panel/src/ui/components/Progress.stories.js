import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Progress",
  decorators: [Padding]
};

export const example = () => ({
  template: `
    <k-progress :value="85" />
  `,
});

export const setValue = () => ({
  template: `
    <div>
      <k-progress ref="progress" :value="50" />

      <k-button-group>
        <k-button @click="$refs.progress.set(0)">0%</k-button>
        <k-button @click="$refs.progress.set(50)">50%</k-button>
        <k-button @click="$refs.progress.set(100)">100%</k-button>
      </k-button-group>
    </div>
  `,
});
