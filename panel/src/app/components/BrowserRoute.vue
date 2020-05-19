<template>
  <k-browser-view
    :fetch="fetch"
    :grid="grid"
  />
</template>
<script>
export default {
  beforeRouteEnter(to, from, next) {
    // if browser support is fine,
    // redirect to the main view
    if (this.$caniuse.all() === true) {
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
