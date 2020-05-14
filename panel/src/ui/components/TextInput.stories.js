import TextInput from "./TextInput.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Input / Text Input",
  component: TextInput,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: ""
    };
  },
  computed: {
    autofocus() {
      return false
    },
    placeholder() {
      return false
    },
    slug() {
      return false;
    },
    trim() {
      return false;
    },
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-text-input
        v-model="value"
        :placeholder="placeholder"
        :slug="slug"
        :trim="trim"
        class="mb-6"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const slug = () => ({
  extends: regular(),
  computed: {
    slug() {
      return true;
    }
  }
});

export const trim = () => ({
  extends: regular(),
  computed: {
    trim() {
      return true;
    }
  }
});

export const placeholder = () => ({
  extends: regular(),
  computed: {
    placeholder() {
      return "Type something …";
    }
  }
});

export const autofocus = () => ({
  extends: regular(),
  computed: {
    autofocus() {
      return true;
    }
  }
});

