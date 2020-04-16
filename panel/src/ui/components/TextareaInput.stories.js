import TextareaInput from "./TextareaInput.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Textarea Input",
  component: TextareaInput,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: "Sad, unstyled input"
    };
  },
  methods: {
    input: action("input"),
    submit: action("submit"),
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const styled = () => ({
  ...regular(),
  data() {
    return {
      value: "This input is prepared to be used in a form field"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const markdown = () => ({
  ...regular(),
  data() {
    return {
      value: "Toolbar buttons will insert markdown instead of Kirbytext"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        markup="markdown"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const monospace = () => ({
  ...regular(),
  data() {
    return {
      value: "This is some monospace text"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        font="monospace"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const dragAndDrop = () => ({
  ...regular(),
  data() {
    return {
      value: ""
    };
  },
  methods: {
    dragstart() {
      this.$store.state.drag = {
        type: "text",
        data: "(file: somefile.jpg)",
      };
    },
    dragend() {
      this.$store.state.drag = false;
    },
    input: action("input"),
    submit: action("submit"),
  },
  template: `
    <div>
      <k-headline class="mb-3">Draggable</k-headline>
      <div class="mb-6">
        <span draggable @dragstart="dragstart" @dragend="dragend">Drag me into the textarea</span>
      </div>

      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const noButtons = () => ({
  ...regular(),
  data() {
    return {
      value: "Just the input without the toolbar"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        :buttons="false"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const placeholder = () => ({
  ...regular(),
  data() {
    return {
      value: ""
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        class="mb-6"
        placeholder="Write something …"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const autofocus = () => ({
  ...regular(),
  data() {
    return {
      value: "This is some content that should be focused"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        :autofocus="true"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const preselect = () => ({
  ...regular(),
  data() {
    return {
      value: "This is some content that should be selected"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        :preselect="true"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  data() {
    return {
      value: "You can't edit me"
    };
  },
  template: `
    <div>
      <k-headline class="mb-3">Input</k-headline>
      <k-textarea-input
        v-model="value"
        :disabled="true"
        class="mb-6"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});



