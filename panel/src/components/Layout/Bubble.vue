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
		<slot name="image">
			<k-item-image v-if="image" :image="image" layout="list" />
			<span v-else />
		</slot>
		<span v-if="text" class="k-bubble-text">{{ text }}</span>
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
:root {
	--bubble-size: 1.525rem;
}

.k-bubble {
	width: min-content;
	height: var(--bubble-size);
	white-space: nowrap;
	line-height: 1.5;
	background: var(--color-light);
	color: var(--color-black);
	border-radius: var(--rounded);
	overflow: hidden;
}
.k-bubble .k-item-figure {
	width: var(--bubble-size);
	height: var(--bubble-size);
}
.k-bubble:has(.k-bubble-text) {
	display: flex;
	gap: var(--spacing-2);
	align-items: center;
	padding-inline-end: 0.5rem;
	font-size: var(--text-xs);
}
</style>
