<template>
	<div :data-gutter="gutter" :data-variant="variant" class="k-grid">
		<!-- @slot All items that will be arranged in the grid -->
		<slot />
	</div>
</template>

<script>
/**
 * The <k-grid> component is a CSS grid wrapper. It goes very well together with the <k-column> component, which allows to set column widths in a very comfortable way. Any other element within the Grid component can be used as well though.
 *
 * Customised the grid via the `--columns` CSS property on `<k-grid>` and the `--width` and/or `--span` properties on its children.
 */
export default {
	props: {
		/**
		 * @deprecated 4.0.0 Use `style="gap: "` or `variant` prop instead
		 * @values "small", "medium", "large", "huge"
		 */
		gutter: String,
		/**
		 * Variants for common grid-spacing use cases
		 * @values "columns", "fields"
		 */
		variant: String
	},
	mounted() {
		if (this.gutter) {
			window.panel.deprecated(
				'<k-grid>: the `gutter` prop will be removed in a future version. Use `style="gap: "` or `variant` prop instead.'
			);
		}
	}
};
</script>

<style>
.k-grid {
	--columns: 12;
	--grid-inline-gap: 0;
	--grid-block-gap: 0;

	display: grid;
	align-items: start;
	grid-column-gap: var(--grid-inline-gap);
	grid-row-gap: var(--grid-block-gap);
}

.k-grid > * {
	--width: calc(1 / var(--columns));
	--span: calc(var(--columns) * var(--width));
}

@container (min-width: 30rem) {
	.k-grid {
		grid-template-columns: repeat(var(--columns), 1fr);
	}

	.k-grid > * {
		grid-column: span var(--span);
	}

	/** @todo grid.gutter.deprecated -Green remove @ 5.0 */
	.k-grid[data-gutter="small"] {
		--grid-inline-gap: 1rem;
		--grid-block-gap: 1rem;
	}
	.k-grid:where(
			[data-gutter="medium"],
			[data-gutter="large"],
			[data-gutter="huge"]
		) {
		--grid-inline-gap: 1.5rem;
		--grid-block-gap: 1.5rem;
	}
}

@container (min-width: 65em) {
	.k-grid[data-gutter="large"] {
		--grid-inline-gap: 3rem;
	}
	.k-grid[data-gutter="huge"] {
		--grid-inline-gap: 4.5rem;
	}
}
@container (min-width: 90em) {
	.k-grid[data-gutter="large"] {
		--grid-inline-gap: 4.5rem;
	}
	.k-grid[data-gutter="huge"] {
		--grid-inline-gap: 6rem;
	}
}
@container (min-width: 120em) {
	.k-grid[data-gutter="large"] {
		--grid-inline-gap: 6rem;
	}
	.k-grid[data-gutter="huge"] {
		--grid-inline-gap: 7.5rem;
	}
}

/** Grid variants **/
:root {
	--columns-inline-gap: clamp(0.75rem, 6cqw, 6rem);
	--columns-block-gap: var(--spacing-8);
}

.k-grid[data-variant="columns"] {
	--grid-inline-gap: var(--columns-inline-gap);
	--grid-block-gap: var(--columns-block-gap);
}
.k-grid:where([data-variant="columns"], [data-variant="fields"]) > * {
	container: column / inline-size;
}

.k-grid[data-variant="fields"] {
	gap: var(--spacing-8);
}

.k-grid[data-variant="choices"] {
	align-items: stretch;
	gap: 2px;
}
</style>
