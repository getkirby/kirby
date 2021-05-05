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
      <k-input
        v-if="options.search"
        v-model="search"
        :autofocus="true"
        :placeholder="$t('search') + ' …'"
        type="text"
        class="k-dialog-search"
        icon="search"
      />

      <template v-if="models.length">
        <k-list>
          <k-list-item
            v-for="file in models"
            :key="file.id"
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
        <k-pagination
          :details="true"
          :dropdown="false"
          v-bind="pagination"
          class="k-dialog-pagination"
          align="center"
          @paginate="paginate"
        />
      </template>
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

<style>
.k-files-dialog .k-list-item {
  cursor: pointer;
}
</style>
