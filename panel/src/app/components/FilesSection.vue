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
        <k-file-rename-dialog ref="renameDialog" />
        <k-file-remove-dialog ref="removeDialog" />
        <k-upload ref="upload" />
      </portal>
    </template>
  </k-model-section>
</template>

<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  created() {
    this.$events.$on("file.delete", this.reload);
    this.$events.$on("file.modify", this.reload);
    this.$events.$on("upload", this.reload);
  },
  destroyed() {
    this.$events.$off("file.delete", this.reload);
    this.$events.$off("file.modify", this.reload);
    this.$events.$off("upload", this.reload);
  },
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
    onOption(option, file = {}, fileIndex) {
      switch (option) {
        case "download":
          window.open(file.url);
          break;
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
          // TODO: fix this
          return this.$refs.upload.open({
            url: endpoint.uploadApi
          });
      }
    }
  }
};
</script>
