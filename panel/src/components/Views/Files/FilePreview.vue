<template>
	<component
		:is="preview"
		v-bind="props"
		:content="content"
		:is-locked="isLocked"
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
		isLocked: Boolean,
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
	--file-preview-color-back: light-dark(
		var(--color-gray-900),
		var(--color-gray-950)
	);
	--file-preview-color-text: var(--color-gray-200);
}

.k-file-preview {
	display: grid;
	align-items: stretch;
	background: var(--file-preview-color-back);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-12);
	overflow: hidden;
}

/** Remove the bottom margin from the preview box if it is followed by tabs */
.k-file-preview:has(+ .k-tabs) {
	margin-bottom: 0;
}
</style>
