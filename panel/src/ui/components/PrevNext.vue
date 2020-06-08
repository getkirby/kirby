<template>
  <k-button-group class="k-prev-next">
    <k-button
      v-bind="prev ? prev : { disabled: true, link: '#' }"
      :icon="prevIcon"
    />
    <k-button
      v-bind="next ? next : { disabled: true, link: '#' }"
      :icon="nextIcon"
    />
  </k-button-group>
</template>

<script>
export default {
  props: {
    /**
     * Define whether the arrows point left-right or up-down.
     * Available options: `horizontal`|`vertical`
     */
    direction: {
      type: String,
      default: "horizontal"
    },
    prev: {
      type: [Boolean, Object],
      default: false
    },
    next: {
      type: [Boolean, Object],
      default: false
    }
  },
  computed: {
    prevIcon() {
      if (this.direction === "vertical") {
        return "angle-up";
      }

      return "angle-left";
    },
    nextIcon() {
      if (this.direction === "vertical") {
        return "angle-down";
      }

      return "angle-right";
    }
  }
};
</script>

<style lang="scss">
.k-prev-next {
  direction: ltr;
}
</style>
