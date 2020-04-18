import TagsField from "./TagsField.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Field / Tags Field",
  component: TagsField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: "",
    };
  },
  computed: {
    options() {
      return [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ];
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-tags-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Tags"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const max = () => ({
  ...regular(),
  template: `
    <div>
      <k-tags-field
        v-model="value"
        :max="3"
        :options="options"
        class="mb-8"
        label="Tags"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-tags-field
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-8"
        label="Tags"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-tags-field
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-8"
        label="Tags"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

