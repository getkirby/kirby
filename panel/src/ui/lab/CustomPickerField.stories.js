import Padding from "../storybook/Padding.js";
import CustomPickerField from "./CustomPickerField.vue";

export default {
  title: "Lab | Custom Picker Field",
  decorators: [Padding]
};

export const Custom = () => ({
  components: {
    "k-custom-picker-field": CustomPickerField
  },
  data() {
    return {
      value: []
    }
  },
  template: `
    <div>
      <k-custom-picker-field
        v-model="value"
        label="Bucket list"
      />
      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});
