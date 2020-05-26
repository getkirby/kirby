import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";
import LanguageRemoveDialog from "./LanguageRemoveDialog.vue";

export default {
  title: "App | Dialogs / Language Remove Dialog",
  component: LanguageRemoveDialog,
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
      <k-headline class="mt-8 mb-3">Languages</k-headline>

      <ul>
        <li
          v-for="language in languages.data"
          :key="language.code"
          class="bg-white p-3 mb-2px shadow rounded-sm flex items-center justify-between"
        >
          {{ language.name }}
          <k-button icon="trash" @click="$refs.dialog.open(language.code)">Delete language</k-button>
        </li>
      </ul>

      <k-language-remove-dialog ref="dialog" @success="load" />

    </div>
  `
});

