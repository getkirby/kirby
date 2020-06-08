<template>
  <k-form-dialog
    ref="dialog"
    v-model="values"
    :loading="isLoading"
    :fields="fieldsetup"
    :cancel-button="cancelButton"
    :submit-button="submitButton"
    @input="onInput"
    @submit="onSubmit"
  >
    <slot />
  </k-form-dialog>
</template>
<script>
import AsyncDialog from "./AsyncDialog.vue";

export default {
  extends: AsyncDialog,
  data() {
    return {
      fields: {},
      values: {}
    };
  },
  computed: {
    fieldsetup() {
      return this.fields;
    }
  },
  methods: {
    onInput(values) {
      this.$emit("input", values);
    },
    async submit() {
      return this.values;
    },
  }
}
</script>
