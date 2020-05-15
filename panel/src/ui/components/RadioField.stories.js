import RadioField from "./RadioField.vue";
import Padding from "../../../storybook/theme/Padding.js";
import Options from "../../../storybook/data/Options.js";

export default {
  title: "UI | Form / Field / Radio Field",
  component: RadioField,
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
      return Options(3);
    }
  },
  template: `
    <div>
      <k-radio-field
        v-model="value"
        :options="options"
        class="mb-8"
        label="Radio"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const info = () => ({
  extends: regular(),
  computed: {
    options() {
      return Options(3, true);
    }
  }
});

export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-radio-field
        v-model="value"
        :autofocus="true"
        :options="options"
        class="mb-8"
        label="Radio"
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
      <k-radio-field
        v-model="value"
        :disabled="true"
        :options="options"
        class="mb-8"
        label="Radio"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const columns = () => ({
  ...regular(),
  template: `
    <div>
      <k-radio-field
        v-model="value"
        :columns="3"
        :options="options"
        class="mb-8"
        label="Radio"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const columnsWithInfo = () => ({
  ...columns(),
  computed: {
    options() {
      return Options(5, true);
    }
  }
});
