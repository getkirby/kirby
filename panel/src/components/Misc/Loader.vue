<template>
  <k-icon class="k-loader" type="loader" />
</template>

<script>
/**
 * Animated loading spinner
 * @public
 *
 * @example <k-loader />
 */
export default {};
</script>

<style>
.k-loader {
  z-index: 1;
}
.k-loader svg {
  animation: Spin 0.9s linear infinite;
}
</style>
