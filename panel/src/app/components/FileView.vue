<template>
  <k-inside class="k-file-view">
    <k-file-preview v-bind="preview" />
    <k-model-view
      :columns="columns"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
      :title="file.filename"
      @rename="onRename"
    >
      <template v-slot:options>
        <k-button
          :responsive="true"
          icon="open"
          @click="onOpen"
        >
          {{ $t("open") }}
        </k-button>
        <k-dropdown>
          <k-button
            :responsive="true"
            :disabled="isLocked"
            icon="cog"
            @click="$refs.settings.toggle()"
          >
            {{ $t('settings') }}
          </k-button>
          <k-dropdown-content
            ref="settings"
            :options="options"
            @option="onOption"
          />
        </k-dropdown>
      </template>
    </k-model-view>

    <k-file-rename-dialog
      ref="rename"
      @success="onRenamed"
    />
    <k-file-remove-dialog
      ref="remove"
      @success="onRemoved"
    />
    <k-upload
      ref="replace"
      @success="onUploaded"
    />

  </k-inside>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    file: {
      type: Object,
      default() {
        return {};
      }
    },
    isLocked: {
      type: Boolean,
      default: false
    },
    tabs: {
      type: Array,
      default() {
        return []
      }
    },
    tab: {
      type: String,
      default: ""
    }
  },
  computed: {
    options() {
      return this.$model.files.options(this.file.parent, this.file.filename, "file");
    },
    preview() {
      return {
        ...this.file,
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
          return this.$refs.rename.open(this.file.parent, this.file.filename);
        case "replace":
          return this.$refs.replace.open({
            url: this.file.replaceApi,
            accept: this.file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.remove.open(this.file.parent, this.file.filename);
      }
    },
    onRename() {
      this.$refs.rename.open(this.file.filename);
    },
    onRenamed() {

    },
    onRemoved() {

    },
    onUploaded() {

    }
  }
};
</script>
