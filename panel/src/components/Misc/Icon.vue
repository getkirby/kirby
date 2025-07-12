<template>
	<span v-if="isEmoji" data-type="emoji">{{ type }}</span>
	<svg
		v-else
		:aria-label="alt"
		:role="alt ? 'img' : null"
		:aria-hidden="!alt"
		:data-type="type"
		class="k-icon"
		:style="{ color: $helper.color(color) }"
	>
		<use :xlink:href="'#icon-' + type" />
	</svg>
</template>

<script>
export const props = {
	props: {
		/**
		 * For better accessibility of icons,
		 * you can pass an additional alt
		 * attribute like for images.
		 */
		alt: String,
		/**
		 * Sets a custom color. Either shorthand
		 * for Panel default colors or actual CSS value.
		 */
		color: String,
		/**
		 * Name of the chosen icon
		 * @see https://getkirby.com/docs/reference/panel/icons
		 */
		type: String
	}
};

/**
 * Display any icon from the Panel's icon set. To combine it with an aspect ratio, background etc. use `<k-icon-frame>`
 *
 * @example <k-icon type="edit" />
 */
export default {
	mixins: [props],
	computed: {
		isEmoji() {
			return this.$helper.string.hasEmoji(this.type);
		}
	}
};
</script>

<style>
:root {
	--icon-size: 18px;
	--icon-color: currentColor;
}

.k-icon {
	width: var(--icon-size);
	height: var(--icon-size);
	flex-shrink: 0;
	color: var(--icon-color);
	fill: currentColor;
}

.k-icon[data-type="loader"] {
	animation: Spin 1.5s linear infinite;
}

@keyframes Spin {
	100% {
		transform: rotate(360deg);
	}
}

/* fix emoji alignment on high-res screens */
@media only screen and (-webkit-min-device-pixel-ratio: 2),
	not all,
	not all,
	not all,
	only screen and (min-resolution: 192dpi),
	only screen and (min-resolution: 2dppx) {
	.k-button-icon [data-type="emoji"] {
		font-size: 1.25em;
	}
}
</style>
