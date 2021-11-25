<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    @cancel="$emit('cancel')"
    @close="$emit('close')"
    @ready="$emit('ready')"
    @submit="$refs.form.submit()"
  >
    <template v-if="text">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="text" />
    </template>
    <k-form
      v-if="hasFields"
      ref="form"
      :value="model"
      :fields="fields"
      :novalidate="novalidate"
      @input="$emit('input', $event)"
      @submit="$emit('submit', $event)"
    />
    <k-box v-else theme="negative"> This form dialog has no fields </k-box>
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
      default: "medium"
    },
    submitButton: {
      type: [String, Boolean],
      default() {
        return window.panel.$t("save");
      }
    },
    text: {
      type: String
    },
    theme: {
      type: String,
      default: "positive"
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
      model: this.value
    };
  },
  computed: {
    hasFields() {
      return Object.keys(this.fields).length > 0;
    }
  },
  watch: {
    value(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.model = newValue;
      }
    }
  }
};
</script>
