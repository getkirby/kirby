<template>
  <k-error-view class="k-browser-view">
    <template v-if="hasFetchSupport === false">
      <p>
        We are really sorry, but your browser does not support
        a feature called <strong>Fetch</strong>, which we us
        to send requests to Kirby's API.
      </p>
      <p>
        You can find a list of supported browsers for this feature on
        <strong><a href="https://caniuse.com/#feat=fetch">caniuse.com</a></strong>
      </p>
    </template>
    <template v-if="hasGridSupport === false">
      <p>
        We are really sorry, but your browser does not support
        a feature called <strong>CSS Grids</strong>, which we
        us to layout our panel interface.
      </p>
      <p>
        You can find a list of supported browsers for this feature on
        <strong>
          <a href="https://caniuse.com/#feat=css-grid">caniuse.com</a>
        </strong>
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
    if (supports.all()) {
      this.$router.push("/");
    }
  }
};
</script>

<style lang="scss">
.k-browser-view .k-error-view-content {
  text-align: left;
}
</style>
