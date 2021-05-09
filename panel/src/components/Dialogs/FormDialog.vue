<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    v-on="$listeners"
  >
    <!-- eslint-disable vue/no-mutating-props -->
    <k-form
      ref="form"
      v-model="value"
      :fields="fields"
      :novalidate="novalidate"
      @input="$emit('input', $event)"
      @submit="$emit('submit', $event)"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import { TranslationString } from '@/config/i18n.js'

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
      type: [String, TranslationString, Boolean],
      default() {
        return new TranslationString("save")
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
