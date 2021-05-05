<template>
  <k-icon
    :type="icon"
    class="k-sort-handle"
    aria-hidden="true"
  />
</template>

<script>
export default {
  props: {
    icon: {
      type: String,
      default: "sort"
    }
  }
}
</script>

<style>
.k-sort-handle {
  cursor: move;
  cursor: grab;
  cursor: -moz-grab;
  cursor: -webkit-grab;
  line-height: 0;
  color: var(--color-gray-900);
  justify-content: center;
  align-items: center;
  line-height: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  will-change: opacity, color;
  transition: opacity .3s;
  z-index: 1;
}
.k-sort-handle svg {
  width: 1rem;
}
.k-sort-handle:active {
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
</style>
