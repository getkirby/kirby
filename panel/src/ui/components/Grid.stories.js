import Grid from "./Grid.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Layout / Grid",
  decorators: [Padding],
  component: Grid
};

export const smallGutter = () => ({
  template: `
    <k-grid gutter="small">
      <k-column
        width="1/1"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/2
      </k-column>
      <k-column
        v-for="n in 2"
        width="1/2"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/2
      </k-column>
      <k-column
        v-for="n in 4"
        width="1/4"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/4
      </k-column>
      <k-column
        v-for="n in 6"
        width="1/6"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/6
      </k-column>
      <k-column
        v-for="n in 12"
        width="1/12"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/12
      </k-column>
    </k-grid>
  `,
});

export const mediumGutter = () => ({
  template: `
    <k-grid gutter="medium">
      <k-column
        width="1/1"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/2
      </k-column>
      <k-column
        v-for="n in 2"
        width="1/2"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/2
      </k-column>
      <k-column
        v-for="n in 4"
        width="1/4"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/4
      </k-column>
      <k-column
        v-for="n in 6"
        width="1/6"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/6
      </k-column>
      <k-column
        v-for="n in 12"
        width="1/12"
        class="bg-white p-6 text-xs font-mono text-center shadow"
      >
        1/12
      </k-column>
    </k-grid>
  `,
});

