<template>
	<div :data-align="align" class="k-bar">
		<!-- @deprecated 4.0.0 left/centre/right slots. Use with default slot only instead -->
		<!-- @todo bar.slots.deprecated - remove specific slots @ 5.0 -->
		<template v-if="$slots.left || $slots.center || $slots.right">
			<div v-if="$slots.left" class="k-bar-slot" data-position="left">
				<!-- @slot Deprecated, use default slot instead -->
				<slot name="left" />
			</div>
			<div v-if="$slots.center" class="k-bar-slot" data-position="center">
				<!-- @slot Deprecated, use default slot instead -->
				<slot name="center" />
			</div>
			<div v-if="$slots.right" class="k-bar-slot" data-position="right">
				<!-- @slot Deprecated, use default slot instead -->
				<slot name="right" />
			</div>
		</template>

		<!-- @slot Contents of the bar -->
		<slot v-else />
	</div>
</template>

<script>
/**
 * The `k-bar` can be used to create  all sorts of toolbars aligning its items accordingly.
 * @public
 *
 * @example
 * <k-bar>
 *   <div></div>
 *   <div></div>
 * </k-bar>
 */
export default {
	props: {
		/**
		 * How to align items horizontally (if not at `start` which is the default)
		 *
		 * @values `center`, `end`
		 */
		align: {
			type: String
		}
	},
	mounted() {
		if (this.$slots.left || this.$slots.center || this.$slots.right) {
			window.panel.deprecated(
				"<k-bar>: left/centre/right slots will be removed in a future version. Use with default slot only instead."
			);
		}
	}
};
</script>

<style>
.k-bar {
	--bar-height: var(--height-xs);
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
	height: var(--bar-height);
	justify-content: space-between;
}

.k-bar:where([data-align="center"]) {
	justify-content: center;
}
.k-bar:where([data-align="end"]):has(:first-child:last-child) {
	justify-content: end;
}

/** @todo bar.slots.deprecated - remove @ 5.0 */
.k-bar-slot {
	flex-grow: 1;
}
.k-bar-slot[data-position="center"] {
	text-align: center;
}
.k-bar-slot[data-position="right"] {
	text-align: end;
}
</style>
