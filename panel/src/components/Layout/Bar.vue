<template>
  <div class="k-bar">
    <div v-if="$slots.left" class="k-bar-slot" data-position="left">
      <slot name="left" />
    </div>
    <div v-if="$slots.center" class="k-bar-slot" data-position="center">
      <slot name="center" />
    </div>
    <div v-if="$slots.right" class="k-bar-slot" data-position="right">
      <slot name="right" />
    </div>
  </div>
</template>

<script>
/**
 * The `k-bar` can be used to create all sorts of toolbars with the option to have three different slots for buttons or other elements: left, center and right.
 * 
 * @example 
 * <k-bar>
 *   <template slot="left">...</template>
 *   <template slot="center">...</template>
 *   <template slot="right">...</template>
 * </k-bar>
 */
export default {}
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
[dir="ltr"] .k-bar-slot[data-position="right"] {
  text-align: right;
}
[dir="rtl"] .k-bar-slot[data-position="right"] {
  text-align: left;
}
</style>
