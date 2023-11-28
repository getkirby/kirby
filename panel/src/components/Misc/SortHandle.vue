<template>
	<k-button
		:title="$t('sort.drag')"
		icon="sort"
		class="k-sort-handle k-sort-button"
		aria-hidden="true"
	/>
</template>

<script>
/**
 * Icon used as handle to drag elements inside the sortable context of `<k-draggable>` with corresponding hover styles.
 *
 * @example <k-sort-handle />
 */
export default {};
</script>

<style>
.k-sort-handle {
	cursor: grab;
	z-index: 1;
}
.k-sort-handle:active {
	cursor: grabbing;
}
</style>
