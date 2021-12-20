<template>
  <k-field v-bind="$props" class="k-pages-field">
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

    <template v-if="selected.length">
      <k-items
        :items="selected"
        :layout="layout"
        :link="link"
        :size="size"
        :sortable="!disabled && selected.length > 1"
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
      </k-items>
    </template>
    <k-empty
      v-else
      :layout="layout"
      :data-invalid="isInvalid"
      icon="page"
      @click="open"
    >
      {{ empty || $t("field.pages.empty") }}
    </k-empty>
    <k-pages-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import picker from "@/mixins/forms/picker.js";

export default {
  mixins: [picker]
};
</script>

<style>
.k-pages-field[data-disabled="true"] * {
  pointer-events: all !important;
}
</style>
