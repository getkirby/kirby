import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Counter",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-counter :count="5" :min="2" :max="10" />
  `
});

export const withInput = () => ({
  data() {
    return {
      text: "Four"
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" :min="2" :max="10" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});

export const exceedingLimits = () => ({
  data() {
    return {
      text: "This is too long"
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" :min="2" :max="10" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});

export const withoutMin = () => ({
  data() {
    return {
      text: "Can be short"
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" :max="15" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});

export const withoutMax = () => ({
  data() {
    return {
      text: "Must be short"
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" :min="5" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});

export const withoutMinMax = () => ({
  data() {
    return {
      text: "Just counting"
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});

export const required = () => ({
  data() {
    return {
      text: ""
    }
  },
  computed: {
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem",
        marginTop: ".5rem"
      };
    }
  },
  template: `
    <div>
      <k-counter :count="text.length" :required="true" />
      <k-text-input v-model="text" :style="styles">
    </div>
  `
});
