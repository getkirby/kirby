import Overlay from "./Overlay.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Overlay",
  decorators: [Padding],
  component: Overlay
};

export const simple = () => ({
  methods: {
    onClose: action("close"),
    onOpen: action("open")
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.overlay.open()">Open</k-button>

      <k-overlay
        ref="overlay"
        @close="onClose"
        @open="onOpen"
      >
        <k-backdrop
          slot-scope="{ close }"
          class="flex items-center justify-center"
          @click="close"
        >
          <p class="bg-white p-6 rounded-sm shadow-lg" @click.stop>This is a simple overlay</p>
        </k-backdrop>
      </k-overlay>
    </div>
  `
});

export const visible = () => ({
  methods: {
    onClose: action("close"),
    onOpen: action("open")
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.overlay.open()">Open</k-button>

      <k-overlay
        ref="overlay"
        :visible="true"
        @close="onClose"
        @open="onOpen"
      >
        <k-backdrop
          slot-scope="{ close }"
          class="flex items-center justify-center"
          @click="close"
        >
          <p class="bg-white p-6 rounded-sm shadow-lg" @click.stop>This is a simple overlay</p>
        </k-backdrop>
      </k-overlay>
    </div>
  `
});

