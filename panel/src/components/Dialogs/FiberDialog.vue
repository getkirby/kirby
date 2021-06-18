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
import { toJson } from "@/api/request.js";

export default {
  props: {
    code: Number,
    component: String,
    path: String,
    props: Object,
  },
  methods: {
    async onSubmit(value) {
      const response = await fetch(this.$url(this.path), {
        body: JSON.stringify(value),
        method: "POST",
        headers: {
          "X-Fiber": true,
        }
      });

      const data   = await toJson(response);
      const dialog = data.$dialog;

      if (dialog) {

        if (dialog.error) {
          this.$refs.dialog.error(dialog.error);
          return;
        }

        if (dialog.code === 200) {
          this.$refs.dialog.close();
          this.$store.dispatch("notification/success", ":)");

          if (dialog.event) {
            if (typeof dialog.event === "string") {
              dialog.event = [dialog.event];
            }

            dialog.event.forEach(() => {
              this.$events.$emit(dialog.event, dialog);
            });
          }

          if (dialog.dispatch) {
            Object.keys(dialog.dispatch).forEach((event) => {
              this.$store.dispatch(event, ...dialog.dispatch[event]);
            });
          }

          if (dialog.redirect) {
            this.$go(dialog.redirect);
          } else {
            this.$reload(dialog.reload || {});
          }
          return;
        }

      } else {
        this.$refs.dialog.close();
        this.$reload();
      }

    }
  }
};
</script>
