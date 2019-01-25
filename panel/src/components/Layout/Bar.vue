<template>
  <div class="k-bar">
    <div v-if="$slots.left" class="k-bar-slot" data-position="left"><slot name="left" /></div>
    <div v-if="$slots.center" class="k-bar-slot" data-position="center"><slot name="center" /></div>
    <div v-if="$slots.right" class="k-bar-slot" data-position="right"><slot name="right" /></div>
  </div>
</template>

<style lang="scss">
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
  [dir="ltr"] & {
    text-align: right;
  }

  [dir="rtl"] & {
    text-align: left;
  }
}
</style>
