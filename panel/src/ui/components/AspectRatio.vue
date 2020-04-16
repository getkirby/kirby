<template>
  <component
    :is="element"
    :data-ratio="ratio"
    :style="'padding-bottom:' + ratioPadding"
    class="k-aspect-ratio"
    v-on="$listeners"
  >
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    element: {
      type: String,
      default: "span",
    },
    ratio: {
      type: String,
      default: "1/1"
    }
  },
  computed: {
    ratioPadding() {
      return this.$helper.ratio(this.ratio || "1/1");
    }
  }
}
</script>
<style lang="scss">
.k-aspect-ratio {
  position: relative;
  display: block;
  line-height: 0;
  padding-bottom: 100%;
}
.k-aspect-ratio > * {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
