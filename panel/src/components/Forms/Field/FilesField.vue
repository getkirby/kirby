<template>
  <k-field v-bind="$props" class="k-files-field">
    <template v-if="more && !disabled" slot="options">
      <k-button-group class="k-field-options">
        <template v-if="uploads">
          <k-dropdown>
            <k-button
              ref="pickerToggle"
              :icon="btnIcon"
              class="k-field-options-button"
              @click="prompt"
            >
              {{ btnLabel }}
            </k-button>
            <k-dropdown-content ref="picker" align="right">
              <k-dropdown-item icon="check" @click="open">
                {{ $t('select') }}
              </k-dropdown-item>
              <k-dropdown-item icon="upload" @click="upload">
                {{ $t('upload') }}
              </k-dropdown-item>
            </k-dropdown-content>
          </k-dropdown>
        </template>
        <template v-else>
          <k-button icon="check" class="k-field-options-button" @click="open">
            {{ $t('select') }}
          </k-button>
        </template>
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
          <k-button
            v-if="!disabled"
            slot="options"
            :tooltip="$t('remove')"
            icon="remove"
            @click="remove(index)"
          />
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
    <k-upload ref="fileUpload" @success="selectUpload" />
  </k-field>
</template>

<script>
import config from "@/config/config.js";
import picker from "@/mixins/picker/field.js";

/**
 * @example <k-files-field v-model="files" name="files" label="Files" />
 */
export default {
  mixins: [picker],
  props: {
    uploads: [Boolean, Object, Array]
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
        this.$refs.picker.toggle();
      } else {
        this.open();
      }
    },
    open() {
      if (this.disabled) {
        return false;
      }

      this.$refs.selector.open({
        endpoint: this.endpoints.field,
        max: this.max,
        multiple: this.multiple,
        search: this.search,
        selected: this.selected.map(file => file.id)
      });
    },
    selectUpload(upload, files) {
      if (this.multiple === false) {
        this.selected = [];
      }

      files.forEach(file => {
        this.selected.push(file);
      });

      this.onInput();
      this.$events.$emit("model.update");
    },
    upload() {
      this.$refs.fileUpload.open({
        url: config.api + "/" + this.endpoints.field + "/upload",
        multiple: this.multiple,
        accept: this.uploads.accept
      });
    }
  }
};
</script>

<style>
.k-files-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
