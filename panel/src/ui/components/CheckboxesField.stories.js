import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import Options from "../../../storybook/data/Options.js";

export default {
  title: "UI | Form / Field / Checkboxes Field",
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: [],
    };
  },
  computed: {
    options() {
      return Options();
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-checkboxes-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Checkboxes"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabled = () => ({
  extends: regular(),
  template: `
    <k-checkboxes-field
      v-model="value"
      :disabled="true"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});

export const autofocus = () => ({
  extends: regular(),
  template: `
    <k-checkboxes-field
      v-model="value"
      :autofocus="true"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});

export const columns = () => ({
  extends: regular(),
  template: `
    <k-checkboxes-field
      v-model="value"
      :columns="3"
      :options="options"
      label="Checkboxes"
      @input="input"
    />
  `,
});

export const checkboxesVsRadios = () => ({
  data() {
    return {
      checkboxes: [],
      radios: [],
    };
  },
  computed: {
    checkboxOptions() {
      return Options();
    },
    radioOptions() {
      return Options();
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <k-grid gutter="large">
      <k-column width="1/2">
        <k-checkboxes-field
          v-model="checkboxes"
          :columns="3"
          :options="checkboxOptions"
          class="mb-6"
          label="Checkboxes"
          @input="input"
        />
        <k-code-block :code="checkboxes" />
      </k-column>
      <k-column width="1/2">
        <k-radio-field
          v-model="radios"
          :columns="3"
          :options="radioOptions"
          class="mb-6"
          label="Radios"
          @input="input"
        />
        <k-code-block :code="radios" />
      </k-column>
    </k-grid>
  `,
});
