<template>
  <k-dialog
    ref="dialog"
    class="k-users-dialog"
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
          :items="items"
          :link="false"
          layout="list"
          :sortable="false"
          @item="toggle"
        >
          <template #options="{ item: user }">
            <k-button v-bind="toggleBtn(user)" @click="toggle(user)" />
          </template>
        </k-items>

        <k-pagination
          :details="true"
          :dropdown="false"
          v-bind="pagination"
          align="center"
          class="k-dialog-pagination"
          @paginate="paginate"
        />
      </template>
      <k-empty v-else icon="users">
        {{ $t("dialog.users.empty") }}
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
import picker from "@/mixins/picker/dialog.js";

export default {
  mixins: [picker],
  methods: {
    item(item) {
      return {
        ...item,
        key: item.email,
        info: item.info !== item.text ? item.info : null
      };
    }
  }
};
</script>

<style>
.k-users-dialog .k-list-item {
  cursor: pointer;
}
</style>
