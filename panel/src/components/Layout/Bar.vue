<template>
	<div :data-align="align" class="k-bar">
		<!--
			@slot Contents of the bar
			@since 4.0.0
		-->
		<slot />
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
</style>

<docs lang="md">
Use the CSS property `--bar-height` to change the height of the bar:

```css
--bar-height: 2rem;
```
</docs>
