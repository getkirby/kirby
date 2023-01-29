<template>
	<div :style="'--width:' + width" :data-sticky="sticky" class="k-column">
		<div><slot /></div>
	</div>
</template>

<script>
/**
 * The Column component can be used within a Grid component to layout elements in a very convenient way. The Grid is based on 12 columns by default and each column can change its width with the width property:
 */
export default {
	props: {
		/**
		 * Width of the column in the grid
		 * @values 1/6, 1/4, 1/3, 1/2, 2/3, 3/4, 5/6, 1/1
		 */
		width: String,
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
	container: inline-size / column;
}

.k-column[data-sticky="true"] > div {
	position: sticky;
	top: 4vh;
	z-index: 2;
}

.k-column[data-disabled="true"] {
	cursor: not-allowed;
	opacity: 0.4;
}
.k-column[data-disabled="true"] * {
	pointer-events: none;
}
.k-column[data-disabled="true"] .k-text[data-theme="help"] * {
	pointer-events: initial;
}
</style>
