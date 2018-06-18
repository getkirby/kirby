<template>
  <div class="kirby-bar">
    <div v-if="$slots.left" class="kirby-bar-slot" data-position="left"><slot name="left" /></div>
    <div v-if="$slots.center" class="kirby-bar-slot" data-position="center"><slot name="center" /></div>
    <div v-if="$slots.right" class="kirby-bar-slot" data-position="right"><slot name="right" /></div>
  </div>
</template>

<style lang="scss">
.kirby-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  line-height: 1;
}
.kirby-bar-slot {
  flex-grow: 1;
}
.kirby-bar-slot[data-position="center"] {
  text-align: center;
}
.kirby-bar-slot[data-position="right"] {
  text-align: right;
}
</style>
