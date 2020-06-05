<template>
  <k-model-section
    ref="section"
    v-bind="$props"
    :empty="emptyOptions"
    :items="files"
    :options="optionOptions"
    type="files"
    @option="onOption"
  >
    <template v-slot:footer>
      <portal>
        <!-- Dialogs -->
        <k-file-rename-dialog
          ref="renameDialog"
          @success="onChanged"
        />
        <k-file-remove-dialog
          ref="removeDialog"
          @success="onChanged"
        />
        <k-upload
          ref="upload"
          @success="onChanged"
        />
      </portal>
    </template>
  </k-model-section>
</template>

<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  computed: {
    emptyDefaults() {
      return {
        icon: "file",
        text: this.$t("files.empty")
      };
    },
    files() {
      return async () => {
        const response = await this.load();

        const items = response.data.map(file => {

          file.options = async ready => ready(await this.$model.files.options(file.parent, file.filename, "list"));

          return file;
        });

        return {
          data: items,
          pagination: response.pagination
        };
      }
    },
    optionOptions() {
      if (this.add === false) {
        return [];
      }

      return [
        { icon: "upload", option: "upload", text: this.$t("upload") },
      ];
    }
  },
  methods: {
    onChanged() {
      this.$refs.section.$refs.collection.reload();
    },
    onOption(option, file = {}, fileIndex) {
      switch (option) {
        case "rename":
          return this.$refs.renameDialog.open(
            file.parent,
            file.filename
          );
        case "replace":
          return this.$refs.upload.open({
            url: this.$config.api + "/" + file.parent + "/files/" + file.filename,
            accept: file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.removeDialog.open(
            file.parent,
            file.filename
          );
        case "upload":
          return this.$refs.upload.open({
            url: endpoint.uploadApi
          });
      }
    }
  }
};
</script>
