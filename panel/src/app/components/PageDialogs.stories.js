import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Dialogs / Page Dialogs",
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      pages: [],
    };
  },
  created() {
    this.load();
  },
  methods: {
    async load() {
      this.pages = await this.$api.pages.children("photography");
    },
    open() {
      this.$refs.dialog.open();
    },
    options(id) {
      return async (ready) => {
        return ready(await this.$model.pages.options(id));
      };
    },
    onOption(option, page) {
      this.$refs[option + "Dialog"].open(page.id);
    }
  },
  template: `
    <div>
      <k-header-bar
        :options="[
          { icon: 'add', text: 'Add page' }
        ]"
        text="Pages"
        @option="$refs.createDialog.open()"
      />
      <ul class="mb-8" data-cy="pages">
        <li
          v-for="page in pages.data"
          :key="page.id"
          class="bg-white mb-2px shadow rounded-sm flex items-center justify-between"
        >
          <span class="p-3">{{ page.title }}</span>
          <k-options-dropdown
            :options="options(page.id)"
            @option="onOption($event, page)"
          />
        </li>
      </ul>
      <k-page-create-dialog ref="createDialog" @success="load" />
      <k-page-duplicate-dialog ref="duplicateDialog" @success="load" />
      <k-page-rename-dialog ref="renameDialog" @success="load" />
      <k-page-remove-dialog ref="removeDialog" @success="load" />
      <k-page-status-dialog ref="statusDialog" @success="load" />
      <k-page-template-dialog ref="templateDialog" @success="load" />
      <k-page-slug-dialog ref="urlDialog" @success="load" />

      <k-headline class="mb-3">DB</k-headline>
      <k-code-block :code="pages.data" />
    </div>
  `,
});
