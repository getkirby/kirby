<template>
	<svg
		:aria-label="alt"
		:role="alt ? 'img' : null"
		:aria-hidden="!alt"
		:data-type="type"
		class="k-icon"
		:style="{ '--color': $helper.color(color) }"
	>
		<use :xlink:href="'#icon-' + type" />
	</svg>
</template>

<script>
/**
 * Use to display any icon from the Panel's icon set.
 * @public
 *
 * @example <k-icon type="edit" />
 */
export default {
	props: {
		/**
		 * For better accessibility of icons,
		 * you can pass an additional alt
		 * attribute like for images.
		 */
		alt: String,
		/**
		 * Sets a custom color. Either shorthand
		 * for Panel default colors or directly
		 * applied CSS value.
		 */
		color: String,
		/**
		 * Select the icon with this attribute
		 */
		type: String
	}
};
</script>

<style>
:root {
	--icon-size: 1rem;
	--icon-color: currentColor;
}

.k-icon {
	width: var(--icon-size);
	height: var(--icon-size);
	flex-shrink: 0;
	color: var(--icon-color);
}

.k-icon[data-type="loader"] {
	animation: Spin 1.5s linear infinite;
}

@keyframes Spin {
	100% {
		transform: rotate(360deg);
	}
}
</style>
