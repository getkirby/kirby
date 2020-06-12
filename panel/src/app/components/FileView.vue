<template>
  <k-inside
    :breadcrumb="breadcrumb"
    :languages="true"
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
          target="_blank"
          icon="open"
        />
        <k-button
          v-if="template !== false"
          v-bind="templateButton"
        />
      </template>
    </k-model-view>

    <!-- Dialogs -->
    <k-file-rename-dialog
      ref="renameDialog"
      @success="$emit('renamed', $event)"
    />
    <k-file-remove-dialog
      ref="removeDialog"
      @success="$emit('removed')"
    />
    <k-upload
      ref="replaceDialog"
      @success="$emit('replaced')"
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
    mime: {
      type: String
    },
    parent: {
      type: String
    },
    preview: {
      type: [Boolean, Object],
      default: false
    },
    template: {
      type: [Boolean, String],
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
    templateButton() {
      return {
        disabled: true,
        icon: {
          type: "template",
          size: "small"
        },
        responsive: true,
        text: this.template,
        tooltip: `${this.$t("template")}: ${this.template}`,
      };
    }
  },
  methods: {
    onOption(option, file) {
      switch (option) {
        case "rename":
          return this.$refs.renameDialog.open(
            this.parent,
            this.filename
          );
        case "replace":
          return this.$refs.replaceDialog.open({
            url: this.$config.api + "/" + this.api,
            accept: this.mime,
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
