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
          v-for="file in models"
          :key="file.filename"
          :text="file.text"
          :info="file.info"
          :image="file.image"
          :icon="file.icon"
          @click="toggle(file)"
        >
          <k-button
            v-if="isSelected(file)"
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
  mixins: [picker]
};
</script>

<style lang="scss">
.k-files-dialog .k-list-item {
  cursor: pointer;
}
</style>
