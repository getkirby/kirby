<template>
	<div :style="{ '--width': width }" :data-sticky="sticky" class="k-column">
		<!-- additional <div> needed to ensure sticky columns behave correctly -->
		<div v-if="sticky">
			<!-- @slot Column content -->
			<slot />
		</div>
		<slot v-else />
	</div>
</template>

<script>
/**
 * The Column component can be used within a <k-grid> component to layout elements in a very convenient way. The Grid is based on 12 columns by default and each column can change its width.
 *
 * @example <k-grid>
 *   <k-column width="2/3">…</k-column>
 *   <k-column width="1/3">…</k-column>
 * </k-grid>
 */
export default {
	props: {
		/**
		 * Width of the column in the grid (as a fraction)
		 * @values e.g. "1/6", "1/4", "1/3", "1/2", "2/3"
		 */
		width: {
			type: String,
			default: "1/1"
		},
		/**
		 * Whether the column should stick to the window edge when scrolling
		 */
		sticky: Boolean
	}
};
</script>

<style>
.k-column {
	min-width: 0;
}

.k-column[data-sticky="true"] {
	align-self: stretch;
}
.k-column[data-sticky="true"] > div {
	position: sticky;
	top: calc(var(--header-sticky-offset) + 2vh);
	z-index: 2;
}
</style>
