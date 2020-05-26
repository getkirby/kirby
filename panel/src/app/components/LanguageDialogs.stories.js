import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Dialogs / Language Dialogs",
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      languages: [],
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
    },
  },
  template: `
    <div>
      <k-header-bar
        :options="[
          { icon: 'add', text: 'Add language' }
        ]"
        text="Languages"
        @option="$refs.createDialog.open()"
      />

      <ul class="mb-8">
        <li
          v-for="language in languages.data"
          :key="language.code"
          class="bg-white mb-2px shadow rounded-sm flex items-center justify-between"
        >
          <span class="p-3">{{ language.name }}</span>
          <nav>
            <k-button class="p-3" icon="edit" @click="$refs.updateDialog.open(language.code)" />
            <k-button class="p-3" icon="trash" @click="$refs.removeDialog.open(language.code)" />
          </nav>
        </li>
      </ul>

      <k-language-create-dialog ref="createDialog" @success="load" />
      <k-language-update-dialog ref="updateDialog" @success="load" />
      <k-language-remove-dialog ref="removeDialog" @success="load" />

      <k-headline class="mb-3">DB</k-headline>
      <k-code-block :code="languages.data" />
    </div>
  `,
});

