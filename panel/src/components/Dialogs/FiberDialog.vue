<template>
  <component
    :is="component"
    ref="dialog"
    :visible="true"
    v-bind="props"
    @cancel="onCancel"
    @submit="onSubmit"
  />
</template>

<script>
export default {
  props: {
    code: Number,
    component: String,
    path: String,
    props: Object,
    referrer: String
  },
  methods: {
    close() {
      this.$refs.dialog.close();
    },
    onCancel() {
      if (typeof this.$store.state.dialog.cancel === "function") {
        this.$store.state.dialog.cancel({ dialog: this });
      }
    },
    async onSubmit(value) {
      let dialog = null;

      try {
        if (typeof this.$store.state.dialog.submit === "function") {
          dialog = await this.$store.state.dialog.submit({
            dialog: this,
            value
          });
        } else if (this.path) {
          dialog = await this.$request(this.path, {
            body: value,
            method: "POST",
            type: "$dialog",
            headers: {
              "X-Fiber-Referrer": this.referrer
            }
          });
        } else {
          throw "The dialog needs a submit action or a dialog route path to be submitted";
        }

        // json parsing failed and
        // the fatal dialog is taking over
        if (dialog === false) {
          return false;
        }

        // everything went fine. We can close the dialog
        this.close();

        // show the smiley in the topbar
        this.$store.dispatch("notification/success", ":)");

        // fire events that might have been defined in the response
        if (dialog.event) {
          if (typeof dialog.event === "string") {
            dialog.event = [dialog.event];
          }
          dialog.event.forEach((event) => {
            this.$events.$emit(event, dialog);
          });
        }

        // dispatch store actions that might have been defined in the response
        if (dialog.dispatch) {
          Object.keys(dialog.dispatch).forEach((event) => {
            const payload = dialog.dispatch[event];
            this.$store.dispatch(
              event,
              Array.isArray(payload) === true ? [...payload] : payload
            );
          });
        }

        // redirect or reload
        if (dialog.redirect) {
          this.$go(dialog.redirect);
        } else {
          this.$reload(dialog.reload || {});
        }
      } catch (e) {
        this.$refs.dialog.error(e);
      }
    }
  }
};
</script>
