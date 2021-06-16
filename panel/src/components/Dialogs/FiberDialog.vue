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

      const data = await toJson(response);

      if (data.$dialog) {

        if (data.$dialog.error) {
          this.$refs.dialog.error(data.$dialog.error);
          return;
        }

        if (data.$dialog.code === 200) {
          this.$refs.dialog.close();
          this.$store.dispatch("notification/success", ":)");

          if (data.$dialog.event) {
            if (typeof data.$dialog.event === "string") {
              data.$dialog.event = [data.$dialog.event];
            }

            data.$dialog.event.forEach(event => {
              this.$events.$emit(data.$dialog.event, data.$dialog);
            });
          }

          if (data.$dialog.redirect) {
            this.$go(data.$dialog.redirect);
          } else {
            this.$reload();
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
