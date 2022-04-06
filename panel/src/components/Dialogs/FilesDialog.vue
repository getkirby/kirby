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

      <k-collection v-bind="collection" @item="toggle" @paginate="paginate">
        <template #options="{ item: file }">
          <k-button v-bind="toggleBtn(file)" @click="toggle(file)" />
        </template>
      </k-collection>
    </template>
  </k-dialog>
</template>

<script>
import picker from "@/mixins/picker/dialog.js";

export default {
  mixins: [picker],
  computed: {
    emptyProps() {
      return {
        icon: "image",
        text: this.$t("dialog.files.empty")
      };
    }
  }
};
</script>

<style>
.k-files-dialog .k-list-item {
  cursor: pointer;
}
</style>
