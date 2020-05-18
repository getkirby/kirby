<template>
  <k-inside class="k-file-view">
    <k-file-preview v-bind="preview" />
    <k-edit-view
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
            @action="action"
          />
        </k-dropdown>
      </template>
    </k-edit-view>

    <k-file-rename-dialog
      ref="rename"
      @success="renamed"
    />
    <k-file-remove-dialog
      ref="remove"
      @success="deleted"
    />
    <k-upload
      ref="upload"
      :url="uploadApi"
      :accept="file.mime"
      :multiple="false"
      @success="uploaded"
    />

  </k-inside>
</template>

<script>
import EditView from "./EditView.vue";
import FileRenameDialog from "./FileRenameDialog.vue";
import FileRemoveDialog from "./FileRemoveDialog.vue";

export default {
  components: {
    "k-edit-view": EditView,
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
    onRename() {
      this.$refs.rename.open(this.file.filename);
    }
  }
};
</script>
