<template>
	<component
		:is="element"
		:data-axis="axis"
		:data-fade="fade"
		class="k-scrollable"
	>
		<slot />
	</component>
</template>

<script>
/**
 * The `k-scrollable` component is a scroll container
 * that keeps its content within the available space
 * and hints at hidden content with a fade on the scrollable
 * edges. Use it wherever content can grow taller (or wider)
 * than its container, e.g. inside dialogs, drawers or collections.
 *
 * @example
 * <k-scrollable><!-- long content --></k-scrollable>
 *
 * @since 5.6.0
 */
export default {
	props: {
		/**
		 * Scroll axis
		 * @values "block", "inline"
		 */
		axis: {
			type: String,
			default: "block"
		},
		/**
		 * The rendered HTML element
		 */
		element: {
			type: String,
			default: "div"
		},
		/**
		 * Which scrollable edges to fade to hint at hidden content.
		 * @values true, false, "start", "end"
		 */
		fade: {
			type: [Boolean, String],
			default: true,
			validator: (value) =>
				typeof value === "boolean" || ["start", "end"].includes(value)
		}
	}
};
</script>

<style>
.k-scrollable {
	--k-scrollable-fade: var(--spacing-6);

	min-block-size: 0;
	min-inline-size: 0;
	overscroll-behavior: contain;
}
.k-scrollable[data-axis="block"] {
	overflow-y: auto;
}
.k-scrollable[data-axis="inline"] {
	overflow-x: auto;
}

@supports (animation-timeline: scroll()) {
	@property --k-scrollable-fade-start {
		syntax: "<length>";
		inherits: false;
		initial-value: 0px;
	}
	@property --k-scrollable-fade-end {
		syntax: "<length>";
		inherits: false;
		initial-value: 0px;
	}

	.k-scrollable[data-fade]:not([data-fade="false"]) {
		--k-scrollable-fade-start-size: var(--k-scrollable-fade);
		--k-scrollable-fade-end-size: var(--k-scrollable-fade);

		animation: k-scrollable-fade-start, k-scrollable-fade-end;
		animation-fill-mode: both;
		animation-range:
			0 var(--k-scrollable-fade),
			calc(100% - var(--k-scrollable-fade)) 100%;
	}
	/* Only fade a single edge; keep sticky content on the other edge sharp */
	.k-scrollable[data-fade="start"] {
		--k-scrollable-fade-end-size: 0px;
	}
	.k-scrollable[data-fade="end"] {
		--k-scrollable-fade-start-size: 0px;
	}
	.k-scrollable[data-fade]:not([data-fade="false"])[data-axis="block"] {
		animation-timeline: scroll(self block);
		mask-image: linear-gradient(
			to bottom,
			transparent,
			#000 var(--k-scrollable-fade-start),
			#000 calc(100% - var(--k-scrollable-fade-end)),
			transparent
		);
	}
	.k-scrollable[data-fade]:not([data-fade="false"])[data-axis="inline"] {
		animation-timeline: scroll(self inline);
		mask-image: linear-gradient(
			to right,
			transparent,
			#000 var(--k-scrollable-fade-start),
			#000 calc(100% - var(--k-scrollable-fade-end)),
			transparent
		);
	}

	@keyframes k-scrollable-fade-start {
		to {
			--k-scrollable-fade-start: var(--k-scrollable-fade-start-size);
		}
	}
	@keyframes k-scrollable-fade-end {
		from {
			--k-scrollable-fade-end: var(--k-scrollable-fade-end-size);
		}
	}
}
</style>
