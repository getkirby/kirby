<template>
  <k-field
    v-bind="$props"
    class="k-blocks-field"
  >
    <k-dropdown v-if="hasFieldsets" slot="options">
      <k-button icon="dots" @click="$refs.options.toggle()" />
      <k-dropdown-content ref="options" align="right">
        <k-dropdown-item :disabled="isFull" icon="add" @click="$refs.blocks.choose(value.length)">
          {{ $t('add') }}
        </k-dropdown-item>
        <k-dropdown-item :disabled="isEmpty" icon="trash" @click="$refs.blocks.confirmToRemoveAll()">
          {{ $t('delete.all') }}
        </k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>

    <k-blocks
      ref="blocks"
      :compact="false"
      :empty="empty"
      :endpoints="endpoints"
      :fieldsets="fieldsets"
      :fieldset-groups="fieldsetGroups"
      :group="group"
      :max="max"
      :value="value"
      @close="opened = $event"
      @open="opened = $event"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    empty: String,
    fieldsets: Object,
    fieldsetGroups: Object,
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
  data() {
    return {
      opened: []
    }
  },
  computed: {
    hasFieldsets() {
      return Object.keys(this.fieldsets).length;
    },
    isEmpty() {
      return this.value.length === 0;
    },
    isFull() {
      if (this.max === null) {
        return false;
      }

      return this.value.length >= this.max;
    }
  }
};
</script>

<style>
.k-blocks-field {
  position: relative;
}
</style>
