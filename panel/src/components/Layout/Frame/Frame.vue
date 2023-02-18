<template>
	<compontent
		:is="element"
		:data-theme="theme"
		:style="{
			'--fit': fit ?? (cover ? 'cover' : 'contain'),
			'--ratio': ratio,
			'--back': background
		}"
		class="k-frame"
	>
		<slot />
	</compontent>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		element: {
			type: String,
			default: "div"
		},
		fit: String,
		ratio: String,
		cover: Boolean,
		back: String,
		theme: String
	},
	computed: {
		background() {
			return this.$helper.color(this.back);
		}
	}
};
</script>

<style>
.k-frame {
	--fit: contain;
	--ratio: 1/1;

	position: relative;
	display: block;
	aspect-ratio: var(--ratio);
	background: var(--back);
	display: grid;
	place-items: center;
	overflow: hidden;
}

.k-frame:where([data-theme]) {
	--back: var(--theme-color-back);
	color: var(--theme-color-text);
}

.k-frame *:where(img, video, iframe, button) {
	position: absolute;
	inset: 0;
	height: 100%;
	width: 100%;
	object-fit: var(--fit);
}
.k-frame > * {
	overflow: hidden;
	text-overflow: ellipsis;
	min-width: 0;
	min-height: 0;
}
</style>
