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

      <template v-if="models.length">
        <k-list>
          <k-list-item
            v-for="user in models"
            :key="user.email"
            :text="user.text"
            :info="user.info !== user.text ? user.info : null"
            :image="user.image"
            :icon="user.icon"
            @click="toggle(user)"
          >
            <k-button
              v-if="isSelected(user)"
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
  mixins: [picker]
};
</script>

<style>
.k-users-dialog .k-list-item {
  cursor: pointer;
}
</style>
