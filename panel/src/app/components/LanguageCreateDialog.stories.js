import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import LanguageCreateDialog from "./LanguageCreateDialog.vue";

export default {
  title: "App | Dialogs / Language Create Dialog",
  component: LanguageCreateDialog,
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      languages: []
    };
  },
  created() {
    this.load();
  },
  methods: {
    async load() {
      this.languages = await this.$api.languages.list();
    },
    open() {
      this.$refs.dialog.open();
    }
  },
  template: `
    <div>
      <k-button @click="open" icon="add">Add a new language</k-button>
      <k-language-create-dialog ref="dialog" @success="load" />

      <k-headline class="mt-8 mb-3">Languages</k-headline>
      <k-code-block :code="languages.data" />
    </div>
  `
});

