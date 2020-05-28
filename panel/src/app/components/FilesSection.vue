<template>
  <k-model-section
    v-bind="$props"
    :options="optionOptions"
    type="files"
    @option="onOption"
  >
    <template v-slot:footer>
      <portal>
        <!-- Dialogs -->
        <k-file-rename-dialog
          ref="renameDialog"
          @success="$emit('renamed', $event)"
        />
        <k-file-remove-dialog
          ref="removeDialog"
          @success="$emit('removed', $event)"
        />
        <k-upload
          ref="replaceDialog"
          @success="$emit('replaced', $event)"
        />
        <k-upload
          ref="uploadDialog"
          @success="$emit('uploaded', $event)"
        />
      </portal>
    </template>
  </k-model-section>
</template>

<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  props: {
    empty: {
      type: Object,
      default() {
        return {
          icon: "file",
          text: this.$t("files.empty")
        };
      }
    }
  },
  computed: {
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
        case "rename":
          return this.$refs.renameDialog.open(
            file.parent.guid,
            file.filename
          );
        case "replace":
          return this.$refs.replaceDialog.open({
            url: file.replaceApi,
            accept: file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.removeDialog.open(
            file.parent.guid,
            file.filename
          );
        case "upload":
          return this.$refs.uploadDialog.open({
            url: endpoint.uploadApi
          });
      }
    }
  }
};
</script>
