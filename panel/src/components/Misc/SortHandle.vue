<template>
  <span
    class="k-sort-handle cursor-grab"
    aria-hidden="true"
  >
    <svg viewBox="0 0 16 16">
      <use xlink:href="#icon-sort" />
    </svg>
  </span>
</template>

<style lang="scss">
.k-sort-handle {
  line-height: 0;
  color: $color-black;
  justify-content: center;
  align-items: center;
  line-height: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  will-change: opacity, color;
  transition: opacity 0.3s;
  z-index: 1;
}
.k-sort-handle svg {
  width: 1rem;
}
</style>
