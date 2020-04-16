import Draggable from "./Draggable.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Interaction / Draggable",
  component: Draggable,
  decorators: [Padding]
};

export const simple = () => ({
  data() {
    return {
      items: ['A', 'B', 'C', 'D']
    }
  },
  computed: {
    styles() {
      return {
        background: "white",
        marginBottom: "2px",
        padding: ".5rem",
        cursor: "move"
      };
    }
  },
  methods: {
    start: action("start"),
    end: action("end")
  },
  template: `
    <div>
      <k-draggable element="ul" :list="items" @start="start" @end="end">
        <li :style="styles" v-for="item in items">{{ item }}</li>
      </k-draggable>

      <br>

      Result: {{ items }}
    </div>
  `,
});

export const withSortHandle = () => ({
  data() {
    return {
      items: ["A", "B", "C", "D"]
    };
  },
  computed: {
    styles() {
      return {
        display: "flex",
        alignItems: "center",
        background: "white",
        marginBottom: "2px",
        padding: ".0625rem 0",
      };
    }
  },
  methods: {
    start: action("start"),
    end: action("end")
  },
  template: `
    <div>
      <k-draggable element="ul" :handle="true" :list="items" @start="start" @end="end">
        <li :style="styles" v-for="item in items">
          <k-sort-handle />
          {{ item }}
        </li>
      </k-draggable>

      <br>

      Result: {{ items }}
    </div>
  `
});

