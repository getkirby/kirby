<template>
  <k-field v-bind="$props" class="k-pages-field">
    <k-button
      v-if="more && !disabled"
      slot="options"
      icon="add"
      @click="open"
    >
      {{ $t('select') }}
    </k-button>
    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :handle="true"
        :list="selected"
        :data-size="size"
        @end="onInput"
      >
        <component
          v-for="(page, index) in selected"
          :is="elements.item"
          :key="page.id"
          :sortable="!disabled && selected.length > 1"
          :text="page.text"
          :info="page.info"
          :link="page.link"
          :icon="page.icon"
          :image="page.image"
        >
          <k-button
            v-if="!disabled"
            slot="options"
            icon="remove"
            @click="remove(index)"
          />
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      :layout="layout"
      icon="page"
      v-on="{ click: !disabled ? open : null }"
    >
      {{ empty || $t('field.pages.empty') }}
    </k-empty>
    <k-pages-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import picker from "@/mixins/picker.js";
import clone from "@/helpers/clone.js";

export default {
  mixins: [picker],
  methods: {
    open() {
      this.$refs.selector.open({
        endpoint: this.endpoints.field,
        max: this.max,
        multiple: this.multiple,
        selected: clone(this.selected)
      });
    }
  }
};
</script>

<style lang="scss">
.k-pages-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
