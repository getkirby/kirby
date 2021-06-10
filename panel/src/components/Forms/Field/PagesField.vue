<template>
  <k-field v-bind="$props" class="k-pages-field">
    <template #options>
      <k-button-group class="k-field-options">
        <k-button
          v-if="more && !disabled"
          :icon="btnIcon"
          class="k-field-options-button"
          @click="open"
        >
          {{ btnLabel }}
        </k-button>
      </k-button-group>
    </template>

    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :handle="true"
        :list="selected"
        :data-size="size"
        :data-invalid="isInvalid"
        @end="onInput"
      >
        <component
          :is="elements.item"
          v-for="(page, index) in selected"
          :key="page.id"
          :sortable="!disabled && selected.length > 1"
          :text="page.text"
          :info="page.info"
          :link="link ? page.link : null"
          :icon="page.icon"
          :image="page.image"
        >
          <template #options>
            <k-button
              v-if="!disabled"
              icon="remove"
              @click="remove(index)"
            />
          </template>
        </component>
      </k-draggable>
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
.k-pages-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
