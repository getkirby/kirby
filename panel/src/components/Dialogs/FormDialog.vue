<template>
  <k-dialog ref="dialog" v-bind="$props" v-on="$listeners" @submit="$refs.form.submit()">
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
      default: "medium",
    },
    submitButton: {
      default() {
        return this.$t('save');
      }
    },
    theme: {
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
