import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Draggable",
  decorators: [Padding]
};

export const simple = () => ({
  data() {
    return {
      items: ['A', 'B', 'C', 'D']
    }
  },
  methods: {
    start: action("start"),
    end: action("end")
  },
  template: `
    <div>
      <k-draggable
        :list="items"
        class="mb-6"
        element="ul"
        @start="start"
        @end="end"
      >
        <li
          v-for="item in items"
          class="cursor-grab p-2 bg-white shadow rounded-sm mb-2px"
        >
          {{ item }}
        </li>
      </k-draggable>

      <k-code-block :code="items" />
    </div>
  `,
});

export const withSortHandle = () => ({
  data() {
    return {
      items: ["A", "B", "C", "D"]
    };
  },
  methods: {
    start: action("start"),
    end: action("end")
  },
  template: `
    <div>
      <k-draggable
        :handle="true"
        :list="items"
        class="mb-6"
        element="ul"
        @start="start"
        @end="end"
      >
        <li
          v-for="item in items"
          class="bg-white shadow rounded-sm mb-2px flex items-center"
        >
          <k-sort-handle />
          {{ item }}
        </li>
      </k-draggable>
      <k-code-block :code="items" />
    </div>
  `
});
