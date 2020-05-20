import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / Grid",
  decorators: [Padding]
};

export const smallGap = () => ({
  template: `
    <k-grid style="--gap: 2px">
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
  `
});

export const mediumGap = () => ({
  template: `
    <k-grid style="--gap: 1.5rem">
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
  `
});

export const colAndRowGap = () => ({
  template: `
    <k-grid style="--col-gap: 2px; --row-gap: 3rem">
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
  `
});
