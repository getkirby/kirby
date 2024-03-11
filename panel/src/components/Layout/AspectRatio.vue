<template>
	<span
		:data-cover="cover"
		:style="{ 'padding-bottom': ratioPadding }"
		class="k-aspect-ratio"
	>
		<!-- @slot Content -->
		<slot />
	</span>
</template>

<script>
/**
 * @deprecated 4.0.0 Use `<k-frame>` instead
 */
export default {
	props: {
		cover: Boolean,
		ratio: String
	},
	computed: {
		ratioPadding() {
			return this.$helper.ratio(this.ratio);
		}
	},
	mounted() {
		window.panel.deprecated(
			"<k-aspect-ratio> will be removed in a future version. Use the <k-frame> instead."
		);
	}
};
</script>

<style>
.k-aspect-ratio {
	position: relative;
	display: block;
	overflow: hidden;
	padding-bottom: 100%;
}
.k-aspect-ratio > * {
	position: absolute !important;
	inset: 0;
	height: 100%;
	width: 100%;
	object-fit: contain;
}
.k-aspect-ratio[data-cover="true"] > * {
	object-fit: cover;
}
</style>
