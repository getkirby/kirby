<template>
	<div :data-align="align" class="k-bar">
		<!-- @todo bar.slots.deprecated - remove specific slots @ 5.0 -->
		<template v-if="$slots.left || $slots.center || $slots.right">
			<div v-if="$slots.left" class="k-bar-slot" data-position="left">
				<!--
					@slot
					@deprecated Use `default` slot instead
				-->
				<slot name="left" />
			</div>
			<div v-if="$slots.center" class="k-bar-slot" data-position="center">
				<!--
					@slot
					@deprecated Use `default` slot instead
				-->
				<slot name="center" />
			</div>
			<div v-if="$slots.right" class="k-bar-slot" data-position="right">
				<!--
					@slot
					@deprecated Use `default` slot instead
				-->
				<slot name="right" />
			</div>
		</template>

		<!--
			@slot Contents of the bar
			@since 4.0.0
		-->
		<slot v-else />
	</div>
</template>

<script>
/**
 * The `k-bar` can be used to create  all sorts of toolbars aligning its items accordingly.
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
		 * How to align items horizontally
		 *
		 * @values "start", "center", "end"
		 */
		align: {
			type: String,
			default: "start"
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
:root {
	--bar-height: var(--height-xs);
}

.k-bar {
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

<docs lang="md">
Use the CSS property `--bar-height` to change the height of the bar:

```css
--bar-height: 2rem;
```
</docs>
