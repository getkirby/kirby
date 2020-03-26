<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    v-on="$listeners"
  >
    <k-form
      ref="form"
      :fields="fields"
      :novalidate="novalidate"
      v-model="value"
      @input="$emit('input', $event)"
      @submit="$emit('submit', $event)"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  props: {
    fields: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    novalidate: {
      type: Boolean,
      default: true
    },
    size: {
      type: String,
      default: "medium",
    },
    submitButton: {
      type: [String, Boolean],
      default() {
        return this.$t('save');
      }
    },
    theme: {
      type: String,
      default: "positive",
    },
    value: {
      type: Object,
      default() {
        return {};
      }
    }
  }
}
</script>
