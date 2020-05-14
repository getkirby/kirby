<template>
  <k-dialog
    ref="dialog"
    v-bind="$props"
    v-on="listeners"
  >
    <k-form
      ref="form"
      :value="value"
      :autofocus="true"
      :fields="fields"
      :novalidate="novalidate"
      class="mb-3"
      @input="onInput"
      @submit="onSubmit"
    />
  </k-dialog>
</template>

<script>
import Dialog from "./Dialog.vue";

export default {
  extends: Dialog,
  props: {
    fields: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    novalidate: {
      type: Boolean,
      default: false
    },
    size: {
      default: "medium"
    },
    submitButton: {
      default() {
        return this.$t("save");
      }
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
        submit: () => {
          this.$refs.form.submit();
        }
      }
    }
  },
  methods: {
    onInput(value) {
      this.$emit("input", value);
    },
    onSubmit(value) {
      this.$emit("submit", value);
    }
  }
}
</script>
