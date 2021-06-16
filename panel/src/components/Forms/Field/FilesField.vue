<template>
  <k-field v-bind="$props" class="k-files-field">
    <template v-if="more && !disabled" #options>
      <k-button-group class="k-field-options">
        <k-options-dropdown
          ref="options"
          v-bind="options"
          @action="onAction"
        />
      </k-button-group>
    </template>

    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        :data-size="size"
        :handle="true"
        :data-invalid="isInvalid"
        @end="onInput"
      >
        <component
          :is="elements.item"
          v-for="(file, index) in selected"
          :key="file.id"
          :sortable="!disabled && selected.length > 1"
          :text="file.text"
          :link="link ? file.link : null"
          :info="file.info"
          :image="file.image"
          :icon="file.icon"
        >
          <template #options>
            <k-button
              v-if="!disabled"
              :tooltip="$t('remove')"
              icon="remove"
              @click="remove(index)"
            />
          </template>
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      :layout="layout"
      :data-invalid="isInvalid"
      icon="image"
      @click="prompt"
    >
      {{ empty || $t("field.files.empty") }}
    </k-empty>

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
        options: [{ icon: "check", text: this.$t("select"), click: "open" }]
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
    prompt(e) {
      e.stopPropagation();

      if (this.disabled) {
        return false;
      }

      if (this.more && this.uploads) {
        this.$refs.options.toggle();
      } else {
        this.open();
      }
    },
    onAction(action) {
      switch (action) {
        case "open":
          return this.open();
        case "upload":
          return this.$refs.fileUpload.open({
            url: this.$urls.api + "/" + this.endpoints.field + "/upload",
            multiple: this.multiple,
            accept: this.uploads.accept
          });
      }
    },
    upload(upload, files) {
      if (this.multiple === false) {
        this.selected = [];
      }

      files.forEach(file => {
        this.selected.push(file);
      });

      this.onInput();
      this.$events.$emit("model.update");
    }
  }
};
</script>

<style>
.k-files-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
