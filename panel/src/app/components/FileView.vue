<template>
  <k-inside class="k-file-view">
    <k-file-preview v-bind="preview" />
    <k-model-view
      :title="file.filename"
      :rename="true"
      :tabs="blueprint.tabs"
      @rename="onRename"
    >
      <template slot="options">
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
      @success="renamed"
    />
    <k-file-remove-dialog
      ref="remove"
      @success="deleted"
    />
    <k-upload
      ref="replace"
      @success="uploaded"
    />

  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";
import FileRenameDialog from "./FileRenameDialog.vue";
import FileRemoveDialog from "./FileRemoveDialog.vue";

export default {
  components: {
    "k-model-view": ModelView,
    "k-file-rename-dialog": FileRenameDialog,
    "k-file-remove-dialog": FileRemoveDialog,
  },
  props: {
    file: {
      type: Object,
      default() {
        return {};
      }
    },
    blueprint: {
      type: Object,
      default() {
        return {}
      }
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
    }
  }
};
</script>
