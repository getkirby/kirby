<template>
	<div :data-gutter="gutter" class="k-grid">
		<slot />
	</div>
</template>

<script>
/**
 * The Grid component is a CSS Grid wrapper. It goes very well together with the `<k-bolumn>` component, which allows to set column widths in a very comfortable way. Any other element within the Grid component can be used as well though.
 */
export default {
	props: {
		/**
		 * @values small, medium, large, huge
		 */
		gutter: String
	}
};
</script>

<style>
.k-grid {
	--column-gap: 0;
	--row-gap: 0;

	display: grid;
	grid-column-gap: var(--column-gap);
	grid-row-gap: var(--row-gap);
}

@container (min-width: 40em) {
	.k-grid {
		--columns: 12;
		--column-gap: 1.5rem;
		--row-gap: 1.5rem;
		grid-template-columns: repeat(var(--columns), 1fr);
	}

	.k-grid[data-gutter="small"] {
		--column-gap: 1rem;
		--row-gap: 1rem;
	}

	.k-grid > * {
		--width: calc(1 / var(--columns));
		--span: calc(var(--columns) * var(--width));
		grid-column: span var(--span);
	}
}

@container (min-width: 50em) {
	.k-grid[data-gutter="large"] {
		--column-gap: 3rem;
	}
	.k-grid[data-gutter="huge"] {
		--column-gap: 4.5rem;
	}
}

@container (min-width: 70em) {
	.k-grid[data-gutter="large"] {
		--column-gap: 4.5rem;
	}
	.k-grid[data-gutter="huge"] {
		--column-gap: 6rem;
	}
}

@container (min-width: 100em) {
	.k-grid[data-gutter="large"] {
		--column-gap: 6rem;
	}
	.k-grid[data-gutter="huge"] {
		--column-gap: 7.5rem;
	}
}
</style>
