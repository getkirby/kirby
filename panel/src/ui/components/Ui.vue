<template>
  <div class="k-ui">
    <k-icons />
    <slot />
  </div>
</template>

<script>
import Vue from "vue";
import Icons from "./Misc/Icons.vue";
import Ui from '../ui.js'

Vue.use(Ui);

export default {
  components: {
    "k-icons": Icons
  }
};

</script>

<style lang="scss">
*,
*::before,
*::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  -webkit-font-smoothing: subpixel-antialiased;
}
html {
  font-family: $font-family-sans;
  background: $color-background;
  color: $color-dark;
}
.k-ui a {
  color: inherit;
  text-decoration: none;
}
.k-ui li {
  list-style: none;
}
.k-ui strong,
.k-ui b {
  font-weight: $font-weight-bold;
}

</style>
