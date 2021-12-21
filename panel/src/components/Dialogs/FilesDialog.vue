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

      <template v-if="items.length">
        <k-items
          :link="false"
          :items="items"
          layout="list"
          :sortable="false"
          @item="toggle"
        >
          <template #options="{ item: file }">
            <k-button v-bind="toggleBtn(file)" @click="toggle(file)" />
          </template>
        </k-items>

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
