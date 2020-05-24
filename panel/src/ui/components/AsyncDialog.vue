<template>
  <k-dialog
    ref="dialog"
    :loading="isLoading"
    :cancel-button="cancelButton"
    :submit-button="submitButton"
    :text="text"
    @submit="onSubmit"
  />
</template>
<script>
export default {
  data() {
    return {
      cancelButton: true,
      isLoading: false,
      submitButton: true,
      text: null
    };
  },
  methods: {
    /**
     * This will directly be forwarded
     * to the Dialog component
     */
    close() {
      return this.$refs.dialog.close();
    },
    /**
     * Loading logic before the dialog
     * gets opened. This is normally used
     * to load a model and inject some
     * data into the dialog.
     */
    async load() {
      return true;
    },
    /**
     * How to react on errors after
     * the submit action? By default
     * the dialog notifaction is triggered
     */
    onError(error) {
      this.$refs.dialog.error(error.message || error);
    },
    /**
     * Handle submitting of the dialog
     * via submitButton or i.e. a form
     */
    async onSubmit(values) {
      this.isLoading = true;
      try {
        await this.validate();
        const response = await this.submit();
        this.isLoading = false;
        this.onSuccess(response);
      } catch (error) {
        this.isLoading = false;
        this.onError(error);
      }
    },
    /**
     * What happens if the submit action succeeded?
     * By default a submit event is fired and
     * the dialog is closed.
     */
    onSuccess(response) {
      this.$emit("submit", response);
      this.$nextTick(() => {
        this.$refs.dialog.close();
      });
    },
    /**
     * Before an async dialog gets opened
     * the load method will be called and
     * the result will be waited for.
     */
    async open(...args) {
      this.isLoading = true;
      try {
        this.$refs.dialog.ready();
        await this.load(...args)
        this.$refs.dialog.open();
      } catch (error) {
        this.$store.dispatch("notification/error", error);
      } finally {
        this.isLoading = false;
      }
    },
    /**
     * Submitting action
     * Probably an API call
     */
    async submit() {
      return true;
    },
    /**
     * Optional validation before
     * the dialog gets submitted.
     * Can be an API call or just
     * some synchronous checks
     */
    async validate() {
      return true;
    }
  }
}
</script>
