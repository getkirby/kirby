<template>
	<div :data-align="align" class="k-bar">
		<!-- @deprecated left/centre/right slots. use with default slot only instead -->
		<!-- @todo remove specific slots in v5.0 -->
		<template v-if="$slots.left || $slots.center || $slots.right">
			<div v-if="$slots.left" class="k-bar-slot" data-position="left">
				<slot name="left" />
			</div>
			<div v-if="$slots.center" class="k-bar-slot" data-position="center">
				<slot name="center" />
			</div>
			<div v-if="$slots.right" class="k-bar-slot" data-position="right">
				<slot name="right" />
			</div>
		</template>

		<slot v-else />
	</div>
</template>

<script>
/**
 * The `k-bar` can be used to create
 * all sorts of toolbars aligning its
 * items accordingly.
 * @public
 *
 * @example
 * <k-bar>
 *   <div></div>
 * 	 <div></div>
 * </k-bar>
 */
export default {
	props: {
		/**
		 * How to align items horizontally, default is `start`
		 * @values `start`, `center`, `end`
		 */
		align: String
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

/** @todo remove in v5.0 */
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
