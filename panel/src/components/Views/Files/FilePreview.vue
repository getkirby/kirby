<template>
	<component :is="preview" v-bind="props" class="k-file-preview" />
</template>

<script>
/**
 * Wrapper for file view previews
 * @since 5.0.0
 */
export default {
	props: {
		component: String,
		props: Object
	},
	computed: {
		preview() {
			if (this.$helper.isComponent(this.component)) {
				return this.component;
			}

			return "k-file-default-preview";
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
