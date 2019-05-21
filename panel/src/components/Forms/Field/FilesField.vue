<template>
  <k-field v-bind="$props" class="k-files-field">
    <k-dropdown slot="options" v-if="more && !disabled">
      <k-button icon="add" @click="$refs.picker.toggle()">{{ $t('add') }}</k-button>
      <k-dropdown-content ref="picker" align="right">
        <k-dropdown-item icon="check" @click="open">{{ $t('select') }}</k-dropdown-item>
        <k-dropdown-item icon="upload" @click="upload">{{ $t('upload') }}</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        :data-size="size"
        :handle="true"
        @end="onInput"
      >
        <component
          v-for="(file, index) in selected"
          :is="elements.item"
          :key="file.filename"
          :sortable="!disabled && selected.length > 1"
          :text="file.text"
          :link="file.link"
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
      icon="image"
      v-on="{ click: !disabled ? open : null }"
    >
      {{ empty || $t('field.files.empty') }}
    </k-empty>

    <k-files-dialog ref="selector" @submit="select" />
    <k-upload ref="fileUpload" @success="selectUpload" />

  </k-field>
</template>

<script>
import config from "@/config/config.js";
import picker from "@/mixins/picker.js";

export default {
  mixins: [picker],
  created() {
    this.$events.$on("file.delete", this.removeById);
  },
  destroyed() {
    this.$events.$off("file.delete", this.removeById);
  },
  methods: {
    open() {
      return this.$api
        .get(this.endpoints.field)
        .then(files => {
          const selectedIds = this.selected.map(file => file.id);

          files = files.map(file => {
            file.selected = selectedIds.indexOf(file.id) !== -1;

            file.thumb = this.image || {};
            file.thumb.url = false;

            if (file.thumbs && file.thumbs.tiny) {
              file.thumb.url = file.thumbs.medium;
            }

            return file;
          });

          this.$refs.selector.open(files, {
            max: this.max,
            multiple: this.multiple
          });
        })
        .catch(() => {
          this.$store.dispatch(
            "notification/error",
            "The files query does not seem to be correct"
          );
        });
    },
    selectUpload(upload, files) {
      files.forEach(file => {
        this.selected.push(file);
      });

      this.$events.$emit("model.update");
    },
    upload() {
      this.$refs.fileUpload.open({
        url: config.api + "/" + this.endpoints.field + "/upload",
        multiple: true,
      });
    }
  }
};
</script>

<style lang="scss">
.k-files-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
