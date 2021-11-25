<template>
  <!-- eslint-disable -->
  <svg
    aria-hidden="true"
    class="k-icons"
    xmlns="http://www.w3.org/2000/svg"
    overflow="hidden"
  >
    <defs>
      <symbol
        v-for="(icon, type) in $options.icons"
        :key="type"
        :id="'icon-' + type"
        viewBox="0 0 16 16"
        v-html="icon"
      />
    </defs>
  </svg>
</template>

<script>
/**
 * @internal
 */
export default {
  icons: window.panel.plugins.icons
};
</script>

<style>
.k-icons {
  position: absolute;
  width: 0;
  height: 0;
}
</style>
