<template>
  <k-inside
    :breadcrumb="breadcrumb"
    :view="view"
    search="files"
    class="k-file-view"
  >
    <k-file-preview
      v-if="preview"
      v-bind="preview"
    />
    <k-model-view
      v-bind="$props"
      :api="apiEndpoint"
      :title="filename"
      @rename="onOption('rename')"
      @option="onOption"
      v-on="$listeners"
    >
      <template v-slot:options>
        <k-button
          v-if="url !== false"
          :link="url"
          :responsive="true"
          :text="$t('open')"
          icon="open"
        />
      </template>
    </k-model-view>

    <!-- Dialogs -->
    <k-file-rename-dialog
      ref="renameDialog"
      @success="$emit('rename', $event)"
    />
    <k-file-remove-dialog
      ref="removeDialog"
      @success="$emit('remove', $event)"
    />
    <k-upload
      ref="replaceDialog"
      @success="$emit('replace', $event)"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    filename: {
      type: String
    },
    parent: {
      type: String
    },
    preview: {
      type: [Boolean, Object],
      default: false
    },
    url: {
      type: [Boolean, String],
      default: false
    },
    view: {
      type: String,
      default: "site"
    }
  },
  computed: {
    apiEndpoint() {
      return this.parent + "/files/" + this.filename;
    }
  },
  methods: {
    onOption(option) {
      switch (option) {
        case "rename":
          return this.$refs.renameDialog.open(
            this.parent,
            this.filename
          );
        case "replace":
          return this.$refs.replaceDialog.open({
            url: this.file.replaceApi,
            accept: this.file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.removeDialog.open(
            this.parent,
            this.filename
          );
      }
    }
  }
};
</script>
