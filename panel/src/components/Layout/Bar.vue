<template>
  <div class="k-bar">
    <div v-if="$slots.left" class="k-bar-slot" data-position="left">
      <!-- @slot Content on the left -->
      <slot name="left" />
    </div>
    <div v-if="$slots.center" class="k-bar-slot" data-position="center">
      <!-- @slot Content in the center -->
      <slot name="center" />
    </div>
    <div v-if="$slots.right" class="k-bar-slot" data-position="right">
      <!-- @slot Content on the right -->
      <slot name="right" />
    </div>
  </div>
</template>

<script>
/**
 * The `k-bar` can be used to create
 * all sorts of toolbars with the option
 * to have three different slots for buttons
 * or other elements: left, center and right.
 * @public
 *
 * @example
 * <k-bar>
 *   <template #left>...</template>
 *   <template #center>...</template>
 *   <template #right>...</template>
 * </k-bar>
 */
export default {};
</script>

<style>
.k-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  line-height: 1;
}
.k-bar-slot {
  flex-grow: 1;
}
.k-bar-slot[data-position="center"] {
  text-align: center;
}
.k-bar-slot[data-position="right"] {
  text-align: end;
}
</style>
