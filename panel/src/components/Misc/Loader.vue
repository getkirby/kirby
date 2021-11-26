<template>
  <span class="k-loader">
    <k-icon class="k-loader-icon" type="loader" />
  </span>
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
.k-loader-icon {
  animation: Spin 0.9s linear infinite;
}
</style>
