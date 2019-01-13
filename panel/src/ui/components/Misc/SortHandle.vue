<template>
  <span class="k-sort-handle">
    <k-icon type="sort" />
  </span>
</template>

<style lang="scss">
.k-sort-handle {
  cursor: -webkit-grab;
  line-height: 0;
  color: $color-dark;
  justify-content: center;
  align-items: center;
  line-height: 0;
  width: 2rem;
  height: 2rem;
  will-change: opacity, color;
  transition: opacity 0.3s;
  z-index: 1;
}
.k-sort-handle .k-icon {
  width: 100%;
  height: 100%;
}
.k-sort-handle:active {
  cursor: -webkit-grabbing;
}
</style>
