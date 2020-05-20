import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Loader",
  decorators: [Padding]
};

export const example = () => ({
  template: `<k-loader />`,
});

export const backgrounds = () => ({
  template: `
    <div>
      <k-loader class="bg-white" style="width: 2.5rem; height: 2.5rem" />
      <k-loader class="bg-black text-white mt-3" style="width: 2.5rem; height: 2.5rem" />
    </div>
  `,
});
