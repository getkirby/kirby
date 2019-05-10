<template>
  <k-field v-bind="$props" class="k-files-field">
    <k-button
      v-if="more && !disabled"
      slot="options"
      icon="add"
      @click="open"
    >
      {{ $t('select') }}
    </k-button>
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
  </k-field>
</template>

<script>
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
  }
};
</script>

<style lang="scss">
.k-files-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
