import TextareaField from "./TextareaField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Field / Textarea Field",
  component: TextareaField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: ""
    };
  },
  methods: {
    input: action("input"),
    submit: action("submit"),
  },
  template: `
    <div>
      <k-textarea-field
        v-model="value"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        class="mb-8"
        label="Text"
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
      <div class="mb-8"
      label="Text">
        <span draggable @dragstart="dragstart" @dragend="dragend">Drag me into the textarea</span>
      </div>

      <k-textarea-field
        v-model="value"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        :buttons="false"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        :autofocus="true"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        :preselect="true"
        class="mb-8"
        label="Text"
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
      <k-textarea-field
        v-model="value"
        :disabled="true"
        class="mb-8"
        label="Text"
        theme="field"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});



