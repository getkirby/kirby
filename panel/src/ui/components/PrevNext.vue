<template>
  <k-button-group class="k-prev-next">
    <k-button
      v-bind="prev"
      :icon="prevIcon"
    />
    <k-button
      v-bind="next"
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
      type: Object,
      default() {
        return {
          disabled: true,
          link: "#",
        }
      }
    },
    next: {
      type: Object,
      default() {
        return {
          disabled: true,
          link: "#"
        }
      }
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
