<template>
  <component
    ref="dialog"
    :is="component"
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
          this.$reload();
          return;
        }

      } else {
        this.$refs.dialog.close();
        this.$go(data.$path);
      }

    }
  }
};
</script>
