<template>
	<component
		:is="preview"
		:content="content"
		v-bind="props"
		class="k-file-preview"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
/**
 * Wrapper for file view previews
 * @since 5.0.0
 */
export default {
	props: {
		component: String,
		content: Object,
		props: Object
	},
	emits: ["input", "submit"],
	computed: {
		preview() {
			if (this.$helper.isComponent(this.component)) {
				return this.component;
			}

			return "k-default-file-preview";
		}
	}
};
</script>
<style>
:root {
	--file-preview-back: var(--color-gray-900);
	--file-preview-text: hsla(0, 100%, var(--color-l-max), 0.75);
}

.k-panel[data-theme="dark"] {
	--file-preview-back: var(--color-gray-100);
	--file-preview-text: hsla(0, 100%, var(--color-l-min), 0.75);
}

.k-file-preview {
	display: grid;
	align-items: stretch;
	background: var(--file-preview-back);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-12);
	overflow: hidden;
}
</style>
