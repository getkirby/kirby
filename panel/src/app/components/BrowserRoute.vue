<template>
  <k-browser-view
    :fetch="fetch"
    :grid="grid"
  />
</template>
<script>
import Vue from "vue";

export default {
  beforeRouteEnter(to, from, next) {
    // if browser support is fine,
    // redirect to the main view
    if (Vue.$caniuse.all() === true) {
      next("/");
    };

    next();
  },
  computed: {
    fetch() {
      return this.$caniuse.fetch();
    },
    grid() {
      return this.$caniuse.grid();
    }
  }
}
</script>
