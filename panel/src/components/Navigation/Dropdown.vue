<template>
  <span class="k-dropdown" @click.stop>
    <slot />
  </span>
</template>

<script>
/**
 * Dropdowns are constructed with two elements: `<k-dropdown>` is used as wrapper to position the dropdown relatively to the button (or any other element) that serves as toggle. `<k-dropdown-content>` holds any content shown when opening the dropdown: any number of `<k-dropdown-item>` elements or any other HTML.
 */
export default {}
</script>

<style>
.k-dropdown {
  position: relative;
}
</style>