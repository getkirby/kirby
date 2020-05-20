import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Autocomplete",
  decorators: [Padding]
};

export const example = () => ({
  computed: {
    options() {
      return [
        { value: "css", text: "CSS" },
        { value: "html", text: "HTML" },
        { value: "js", text: "Javascript" },
        { value: "php", text: "PHP" },
        { value: "vue", text: "Vue.js" },
      ];
    },
    styles() {
      return {
        border: "1px solid #ddd",
        background: "#fff",
        padding: ".5rem"
      };
    }
  },
  methods: {
    close: action("close"),
    open: action("open"),
    search: action("search"),
    select: action("select"),
  },
  template: `
    <k-autocomplete
      ref="autocomplete"
      :options="options"
      @close="close"
      @search="search"
      @select="select"
      @open="open"
    >
      <k-text-input
        :style="styles"
        @input="$refs.autocomplete.search($event)"
      />
    </k-autocomplete>
  `
});
