import TagsInput from "./TagsInput.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Form / Input / Tags Input",
  component: TagsInput
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
      <k-tags-input
        v-model="value"
        :options="options"
        @input="input"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});


