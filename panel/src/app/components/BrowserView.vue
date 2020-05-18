<template>
  <k-error-view class="k-browser-view">
    <p>
      We are really sorry, but your browser does not support
      all features required for the Kirby Panel.
    </p>
    <template v-if="fetch === false">
      <p>
        <strong>Fetch</strong><br>
        We use Javascript's new Fetch API. You can find a list of supported browsers for this feature on
        <strong><a href="https://caniuse.com/#feat=fetch">caniuse.com</a></strong>
      </p>
    </template>
    <template v-if="grid === false">
      <p>
        <strong>CSS Grid</strong><br>
        We use CSS Grids for all our layouts. You can find a list of supported browsers for this feature on
        <strong><a href="https://caniuse.com/#feat=css-grid">caniuse.com</a></strong>
      </p>
    </template>
  </k-error-view>
</template>

<script>
export default {
  props: {
    fetch: {
      type: Boolean,
      default: true,
    },
    grid: {
      type: Boolean,
      default: true
    }
  }
};
</script>
