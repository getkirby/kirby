<template>
  <k-field v-bind="$props" class="k-users-field">
    <template #options>
      <k-button-group class="k-field-options">
        <k-button
          v-if="more && !disabled"
          :icon="btnIcon"
          :text="btnLabel"
          class="k-field-options-button"
          @click="open"
        />
      </k-button-group>
    </template>

    <k-collection
      v-bind="collection"
      @empty="open"
      @sort="onInput"
      @sortChange="$emit('change', $event)"
    >
      <template #options="{ index }">
        <k-button
          v-if="!disabled"
          :tooltip="$t('remove')"
          icon="remove"
          @click="remove(index)"
        />
      </template>
    </k-collection>

    <k-users-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import picker from "@/mixins/forms/picker.js";

export default {
  mixins: [picker],
  computed: {
    emptyProps() {
      return {
        icon: "users",
        text: this.empty || this.$t("field.users.empty")
      };
    }
  }
};
</script>

<style>
.k-users-field[data-disabled="true"] * {
  pointer-events: all !important;
}
</style>
