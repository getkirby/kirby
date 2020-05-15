import Offline from "./Offline.vue";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Error Handling / Offline warning",
  decorators: [Padding],
  component: Offline
};

export const simulated = () => ({
  mounted() {
    this.$events.$emit("offline");
  },
  template: `
    <k-offline />
  `
});

export const manual = () => ({
  template: `
    <div>
      <k-box theme="info">Disconnect your computer from the network to test the offline warning</k-box>
      <k-offline />
    </div>
  `
});

export const disabled = () => ({
  template: `
    <div>
      <k-box theme="info">Disconnect your computer from the network to test the offline warning</k-box>
      <k-offline :disabled="true" />
    </div>
  `
});
