<template>
  <k-field v-bind="$props" class="k-files-field">
    <template v-if="more && !disabled" #options>
      <k-button-group class="k-field-options">
        <k-options-dropdown ref="options" v-bind="options" @action="onAction" />
      </k-button-group>
    </template>

    <k-dropzone :disabled="!moreUpload" @drop="drop">
      <k-collection
        v-bind="collection"
        @empty="prompt"
        @sort="onInput"
        @sortChange="$emit('change', $event)"
      >
        <template #options="{ index }">
          <k-button
            v-if="!disabled"
            :tooltip="$t('remove')"
            icon="remove"
            @click="remove(index)"
          />
        </template>
      </k-collection>
    </k-dropzone>

    <k-files-dialog ref="selector" @submit="select" />
    <k-upload ref="fileUpload" @success="upload" />
  </k-field>
</template>

<script>
import picker from "@/mixins/forms/picker.js";

/**
 * @example <k-files-field v-model="files" name="files" label="Files" />
 */
export default {
  mixins: [picker],
  props: {
    uploads: [Boolean, Object, Array]
  },
  computed: {
    emptyProps() {
      return {
        icon: "image",
        text: this.empty || this.$t("field.files.empty")
      };
    },
    moreUpload() {
      return !this.disabled && this.more && this.uploads;
    },
    options() {
      if (this.uploads) {
        return {
          icon: this.btnIcon,
          text: this.btnLabel,
          options: [
            { icon: "check", text: this.$t("select"), click: "open" },
            { icon: "upload", text: this.$t("upload"), click: "upload" }
          ]
        };
      }

      return {
        options: [
          { icon: "check", text: this.$t("select"), click: () => this.open() }
        ]
      };
    },
    uploadParams() {
      return {
        accept: this.uploads.accept,
        max: this.max,
        multiple: this.multiple,
        url: this.$urls.api + "/" + this.endpoints.field + "/upload"
      };
    }
  },
  created() {
    this.$events.$on("file.delete", this.removeById);
  },
  destroyed() {
    this.$events.$off("file.delete", this.removeById);
  },
  methods: {
    drop(files) {
      if (this.uploads === false) {
        return false;
      }

      return this.$refs.fileUpload.drop(files, this.uploadParams);
    },
    prompt(e) {
      e.stopPropagation();

      if (this.disabled) {
        return false;
      }

      if (this.moreUpload) {
        this.$refs.options.toggle();
      } else {
        this.open();
      }
    },
    onAction(action) {
      // no need for `action` modifier
      // as native button `click` prop requires
      // inline function when only one option available
      if (!this.moreUpload) {
        return;
      }

      switch (action) {
        case "open":
          return this.open();
        case "upload":
          return this.$refs.fileUpload.open(this.uploadParams);
      }
    },
    isSelected(file) {
      return this.selected.find((f) => f.id === file.id);
    },
    upload(upload, files) {
      if (this.multiple === false) {
        this.selected = [];
      }

      files.forEach((file) => {
        if (!this.isSelected(file)) {
          this.selected.push(file);
        }
      });

      this.onInput();
      this.$events.$emit("model.update");
    }
  }
};
</script>

<style>
.k-files-field[data-disabled="true"] * {
  pointer-events: all !important;
}
</style>
