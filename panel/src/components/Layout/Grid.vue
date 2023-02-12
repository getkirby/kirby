<template>
	<div :data-gutter="gutter" :data-variant="variant" class="k-grid">
		<slot />
	</div>
</template>

<script>
/**
 * The Grid component is a CSS Grid wrapper. It goes very well together with the `<k-column>` component, which allows to set column widths in a very comfortable way. Any other element within the Grid component can be used as well though.
 */
export default {
	props: {
		/**
		 * @deprecated Use `style="gap: "` or `variant` prop instead
		 * @todo Remove in v5.0
		 * @values small, medium, large, huge
		 */
		gutter: String,
		/**
		 * @values `columns`, `fields`
		 */
		variant: String
	}
};
</script>

<style>
.k-grid {
	--columns: 12;

	display: grid;
	align-items: start;
}

.k-grid > * {
	--width: calc(1 / var(--columns));
	--span: calc(var(--columns) * var(--width));
	grid-column: span var(--span);
}

@container (min-width: 50rem) {
	.k-grid {
		grid-template-columns: repeat(var(--columns), 1fr);
	}
}

/** @deprecated: Gutter **/
/** @todo remove in v5.0 */
@media screen and (min-width: 30em) {
	.k-grid[data-gutter="small"] {
		grid-column-gap: 1rem;
		grid-row-gap: 1rem;
	}
	.k-grid[data-gutter="medium"],
	.k-grid[data-gutter="large"],
	.k-grid[data-gutter="huge"] {
		grid-column-gap: 1.5rem;
		grid-row-gap: 1.5rem;
	}
}

@media screen and (min-width: 65em) {
	.k-grid[data-gutter="large"] {
		grid-column-gap: 3rem;
	}
	.k-grid[data-gutter="huge"] {
		grid-column-gap: 4.5rem;
	}
}
@media screen and (min-width: 90em) {
	.k-grid[data-gutter="large"] {
		grid-column-gap: 4.5rem;
	}
	.k-grid[data-gutter="huge"] {
		grid-column-gap: 6rem;
	}
}
@media screen and (min-width: 120em) {
	.k-grid[data-gutter="large"] {
		grid-column-gap: 6rem;
	}
	.k-grid[data-gutter="huge"] {
		grid-column-gap: 7.5rem;
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
