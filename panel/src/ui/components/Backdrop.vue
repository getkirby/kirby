<template>
  <div
    class="k-backdrop"
    v-on="$listeners"
  >
    <slot />
  </div>
</template>

<style lang="scss">
.k-backdrop {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: $color-backdrop;
  z-index: z-index(dialog);
  transform: translate3d(0, 0, 0);
}
</style>
