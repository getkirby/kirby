<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    v-on="listeners"
  >
    <k-form
      ref="form"
      v-model="value"
      :fields="fields"
      :novalidate="novalidate"
      @input="onInput"
      @submit="onSubmit"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "../mixins/dialog.js";

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
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        submit: this.onSubmit
      }
    }
  },
  methods: {
    onInput(input) {
      this.$emit("input", this.value);
    },
    onSubmit(event) {
      this.$emit("submit", this.value);
    }
  }
}
</script>
