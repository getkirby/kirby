<template>
  <component
    :is="component"
    ref="dialog"
    :visible="true"
    v-bind="props"
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
    async onSubmit(value) {
      try {
        const dialog = await this.$request(this.path, {
          body: value,
          method: "POST",
          type: "$dialog",
          headers: {
            "X-Fiber-Referrer": this.referrer
          }
        });

        // json parsing failed and
        // the fatal dialog is taking over
        if (dialog === false) {
          return false;
        }

        // everything went fine. We can close the dialog
        this.$refs.dialog.close();

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
            this.$store.dispatch(event, ...dialog.dispatch[event]);
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
