<template>
  <div
    :class="'k-backdrop ' + background"
    v-on="$listeners"
  >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    isDimmed: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    background() {
      return this.isDimmed ? "bg-backdrop" : null;
    }
  }
}
</script>

<style lang="scss">
.k-backdrop {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: z-index(dialog);
  transform: translate3d(0, 0, 0);
}
</style>
