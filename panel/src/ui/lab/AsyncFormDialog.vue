<template>
  <k-form-dialog
    ref="dialog"
    v-model="values"
    :loading="loading"
    :fields="fields"
    :cancel-button="cancelButton"
    :submit-button="submitButton"
    @input="onInput"
    @submit="onSubmit"
  />
</template>
<script>
export default {
  data() {
    return {
      loading: false,
      values: {}
    };
  },
  computed: {
    cancelButton() {
      return true;
    },
    fields() {
      return {};
    },
    submitButton() {
      return true;
    }
  },
  methods: {
    close() {
      return this.$refs.dialog.close();
    },
    async load() {
      return {};
    },
    onInput(values) {
      this.$emit("input", values);
    },
    async onSubmit(values) {
      this.loading = true;

      try {
        await this.validate(values);
        await this.submit(values);
        this.loading = false;
        this.$emit("submit", values);
        this.$refs.dialog.close();
      } catch (e) {
        this.loading = false;
        this.$refs.dialog.error(e.message || e);
      }
    },
    async open() {
      try {
        this.values = await this.load();
        this.$refs.dialog.open();
      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async submit(values) {
      return true;
    },
    async validate(values) {
      return true;
    }
  }
}
</script>
