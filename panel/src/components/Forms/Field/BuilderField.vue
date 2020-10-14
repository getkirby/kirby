<template>
  <k-field
    v-bind="$props"
    class="k-builder-field"
  >
    <!-- <k-dropdown slot="options">
      <k-button icon="cog" @click="$refs.options.toggle()" />
      <k-dropdown-content ref="options" align="right">
        <k-dropdown-item :disabled="isFull" icon="add" @click="select(blocks.length)">
          {{ $t('add') }}
        </k-dropdown-item>
        <k-dropdown-item :disabled="isEmpty" :icon="hasOpened ? 'collapse' : 'expand'" @click="toggleAll()">
          {{ hasOpened ? $t('collapse.all') : $t('expand.all') }}
        </k-dropdown-item>
        <hr>
        <k-dropdown-item :disabled="isEmpty" icon="trash" @click="$refs.removeAll.open()">
          {{ $t('delete.all') }}
        </k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown> -->

    <k-builder-blocks
      ref="blocks"
      :empty="empty"
      :endpoints="endpoints"
      :fieldsets="fieldsets"
      :group="group"
      :max="max"
      :value="value"
      v-on="$listeners"
    />

  </k-field>
</template>

<script>
import Vue from "vue";

import Field from "../Field.vue";
import Blocks from "./BuilderField/Blocks.vue";

Vue.component("k-builder-blocks", Blocks);

export default {
  inheritAttrs: false,
  components: {
    "k-builder-blocks": Blocks,
  },
  props: {
    ...Field.props,
    empty: String,
    fieldsets: Object,
    group: String,
    max: {
      type: Number,
      default: null,
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
};
</script>

<style lang="scss">
.k-builder-field {
  position: relative;
}
.k-builder-field .k-sortable-ghost {
  outline: 2px solid $color-focus;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-builder-field-empty {
  cursor: pointer;

  .k-builder-blocks > &:not(:only-child) {
    display: none;
  }
}
</style>
