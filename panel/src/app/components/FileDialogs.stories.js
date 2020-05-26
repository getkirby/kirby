import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Dialogs / File Dialogs",
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      files: [],
    };
  },
  created() {
    this.load();
  },
  methods: {
    async load() {
      this.files = await this.$api.pages.files("photography/animals");
    },
    open() {
      this.$refs.dialog.open();
    },
    onOption(option, file) {
      this.$refs[option + "Dialog"].open("pages/" + file.parent.id, file.filename);
    },
    upload() {
      this.$refs.upload.open({
        url: "/api/pages/photography+animals/files",
        multiple: false
      });
    }
  },
  template: `
    <div>
      <k-header-bar
        :options="[
          { icon: 'upload', text: 'Upload file' }
        ]"
        text="Files"
        @option="upload"
      />
      <ul class="mb-8">
        <li
          v-for="file in files.data"
          :key="file.id"
          class="bg-white mb-2px shadow rounded-sm flex items-center justify-between"
        >
          <span class="p-3">{{ file.filename }}</span>
          <k-options-dropdown
            :options="[
              { icon: 'title', text: 'Rename file', option: 'rename' },
              { icon: 'trash', text: 'Delete file', option: 'remove' },
            ]"
            @option="onOption($event, file)"
          />
        </li>
      </ul>
      <k-file-rename-dialog ref="renameDialog" @success="load" />
      <k-file-remove-dialog ref="removeDialog" @success="load" />
      <k-upload ref="upload" @success="load" />

      <k-headline class="mb-3">DB</k-headline>
      <k-code-block :code="files.data" />
    </div>
  `,
});

