<template>
	<component
		:is="link ? 'k-link' : 'p'"
		:to="link"
		:style="{
			color: $helper.color(color),
			background: $helper.color(back)
		}"
		class="k-bubble"
		@click.native.stop
	>
		<k-item-image v-if="image" :image="image" layout="list" />
		{{ text }}
	</component>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		back: String,
		color: String,
		element: {
			type: String,
			default: "li"
		},
		image: Object,
		link: String,
		text: String
	}
};
</script>

<style>
.k-bubble {
	display: flex;
	padding: 0 0.5rem;
	white-space: nowrap;
	align-items: center;
	line-height: 1.5;
	font-size: var(--text-xs);
	height: 1.525rem;
	background: var(--color-light);
	color: var(--color-black);
	border-radius: var(--rounded);
	overflow: hidden;
}
.k-bubble .k-item-figure {
	width: 1.525rem;
	height: 1.525rem;
	margin-inline-start: -0.5rem;
	margin-inline-end: var(--spacing-2);
}
</style>
