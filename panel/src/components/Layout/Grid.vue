<template>
	<div :data-variant="variant" class="k-grid">
		<slot />
	</div>
</template>

<script>
/**
 * @todo breaking change: removed `gutter` prop/data variant to set gap size
 */

/**
 * The Grid component is a CSS Grid wrapper. It goes very well together with the `<k-bolumn>` component, which allows to set column widths in a very comfortable way. Any other element within the Grid component can be used as well though.
 */
export default {
	props: {
		/**
		 * @values `columns`, `fields`
		 */
		variant: String
	}
};
</script>

<style>
.k-grid {
	display: grid;
	align-items: start;
}

@container (min-width: 50rem) {
	.k-grid {
		--columns: 12;
		grid-template-columns: repeat(var(--columns), 1fr);
	}

	.k-grid > * {
		--width: calc(1 / var(--columns));
		--span: calc(var(--columns) * var(--width));
		grid-column: span var(--span);
	}
}

/** Grid variants **/
.k-grid[data-variant="columns"] {
	column-gap: clamp(0.75rem, 5cqw, 6rem);
	row-gap: clamp(1.5rem, 5cqh, 3rem);
}
.k-grid[data-variant="columns"] > * {
	container: column / inline-size;
}

.k-grid[data-variant="fields"] {
	gap: var(--spacing-6);
}
</style>
