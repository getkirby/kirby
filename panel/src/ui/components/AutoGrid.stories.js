import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / Auto Grid",
  decorators: [Padding]
};

export const example = () => ({
  template: `
    <div>
      <k-headline class="mb-3">min: 5rem</k-headline>
      <k-auto-grid style="--min: 5rem; --gap: 1.5rem" class="mb-10">
        <div
          v-for="n in 10"
          class="bg-white p-6 text-xs font-mono text-center shadow"
        />
      </k-auto-grid>

      <k-headline class="mb-3">min: 10rem</k-headline>
      <k-auto-grid style="--min: 10rem; --gap: 1.5rem" class="mb-10">
        <div
          v-for="n in 10"
          class="bg-white p-6 text-xs font-mono text-center shadow"
        />
      </k-auto-grid>

      <k-headline class="mb-3">min: 25rem</k-headline>
      <k-auto-grid style="--min: 25rem; --gap: 1.5rem" class="mb-10">
        <div
          v-for="n in 10"
          class="bg-white p-6 text-xs font-mono text-center shadow"
        />
      </k-auto-grid>
    </div>
  `
});
