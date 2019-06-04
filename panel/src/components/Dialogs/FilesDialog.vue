<template>
  <k-dialog
    ref="dialog"
    class="k-files-dialog"
    size="medium"
    @cancel="$emit('cancel')"
    @submit="submit"
  >

    <template v-if="issue">
      <k-box :text="issue" theme="negative" />
    </template>

    <template v-else>
      <k-list v-if="models.length">
        <k-list-item
          v-for="(file, index) in models"
          :key="file.filename"
          :text="file.filename"
          :image="file.image"
          :icon="file.icon"
          @click="toggle(index)"
        >
          <k-button
            v-if="file.selected"
            slot="options"
            :autofocus="true"
            :icon="checkedIcon"
            :tooltip="$t('remove')"
            theme="positive"
          />
          <k-button
            v-else
            slot="options"
            :autofocus="true"
            :tooltip="$t('select')"
            icon="circle-outline"
          />
        </k-list-item>
      </k-list>

      <k-empty v-else icon="image">
        {{ $t("dialog.files.empty") }}
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
import picker from "@/mixins/picker/dialog.js";

export default {
  mixins: [picker],
  methods: {
    isFiltered(file) {
      return file.filename.includes(this.search);
    },
    isSelected(file, selected) {
      return selected.indexOf(file.id) !== -1;
    },
    onFetched() {
      this.models = this.models.map(file => {
        file.thumb = this.options.image || {};
        file.thumb.url = false;

        if (file.thumbs && file.thumbs.tiny) {
          file.thumb.url = file.thumbs.medium;
        }

        return file;
      });
    }
  }
};
</script>

<style lang="scss">
.k-files-dialog .k-list-item {
  cursor: pointer;
}
.k-files-dialog-search {
  margin-bottom: 0.5rem;
}
</style>
