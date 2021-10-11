<template>
  <k-icon :type="icon" class="k-sort-handle" aria-hidden="true" />
</template>

<script>
/**
 * Icon used as handle to drag elements inside
 * the sortable context of `<k-draggable>` with
 * corresponding hover styles.
 * @public
 *
 * @example <k-sort-handle />
 */
export default {
  props: {
    /**
     * Icon used for the handle
     * @values see `<k-icon>` for options
     */
    icon: {
      type: String,
      default: "sort"
    }
  }
};
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
  transition: opacity 0.3s;
  z-index: 1;
}
.k-sort-handle svg {
  width: 1rem;
  height: 1rem;
}
.k-sort-handle:active {
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
</style>
