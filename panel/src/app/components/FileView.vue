<template>
  <k-inside
    :breadcrumb="breadcrumb"
    :view="view"
    class="k-file-view"
  >
    <k-file-preview v-bind="preview" />
    <k-model-view
      v-bind="$props"
      :rename="true"
      :title="file.filename"
      @rename="onOption('rename')"
      @option="onOption"
    >
      <template v-slot:options>
        <k-button
          :responsive="true"
          :text="$t('open')"
          icon="open"
          @click="onOpen"
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
      @success="$emit('removed', $event)"
    />
    <k-upload
      ref="replaceDialog"
      @success="$emit('replaced', $event)"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    file: {
      type: Object,
      default() {
        return {};
      }
    },
    view: {
      type: String,
      default: "site"
    }
  },
  computed: {
    preview() {
      return {
        ...this.file,
        ...this.file.dimensions || {},
        image: this.file.url,
        link: this.file.url,
        size: this.file.niceSize,
      };
    }
  },
  methods: {
    onOpen() {
      window.open(this.file.url);
    },
    onOption(option) {
      switch (option) {
        case "rename":
          return this.$refs.renameDialog.open(
            this.file.parent.guid,
            this.file.filename
          );
        case "replace":
          return this.$refs.replaceDialog.open({
            url: this.file.replaceApi,
            accept: this.file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.removeDialog.open(
            this.file.parent.guid,
            this.file.filename
          );
      }
    }
  }
};
</script>
