<template>
  <k-error-view class="k-browser-view">
    <p>
      We are really sorry, but your browser does not support
      all features required for the Kirby Panel.
    </p>

    <template v-if="hasFetchSupport === false">
      <p>
        <strong>Fetch</strong><br>
        We use Javascript's new Fetch API. You can find a list of supported browsers for this feature on
        <strong><a href="https://caniuse.com/#feat=fetch">caniuse.com</a></strong>
      </p>
    </template>
    <template v-if="hasGridSupport === false">
      <p>
        <strong>CSS Grid</strong><br>
        We use CSS Grids for all our layouts. You can find a list of supported browsers for this feature on
        <strong><a href="https://caniuse.com/#feat=css-grid">caniuse.com</a></strong>
      </p>
    </template>
  </k-error-view>
</template>

<script>
import supports from "@/config/supports.js";

export default {
  computed: {
    hasFetchSupport() {
      return supports.fetch();
    },
    hasGridSupport() {
      return supports.grid();
    }
  },
  created() {
    this.$store.dispatch("content/current", null);

    if (supports.all()) {
      this.$go("/");
    }
  }
};
</script>

<style>
.k-browser-view .k-error-view-content {
  text-align: left;
}
</style>
