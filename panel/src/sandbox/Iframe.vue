<template>
  <component :is="'sandbox-' + $route.params.component" ref="el" />
</template>

<script>
export default {
  mounted() {
    const emit = this.$refs.el.$emit;
    this.$refs.el.$emit = (...args) => {

      if (args[0] || null) {
        window.console.log(`Event: ${args[0]}`);
      }

      if (args[1] || null) {
        window.console.log(args[1]);
      }

      emit.apply(this, args);
    }
  }
}
</script>
