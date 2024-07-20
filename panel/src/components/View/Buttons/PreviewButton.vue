<template>
	<k-view-button
		v-if="isAvailable"
		:link="link"
		:title="$t('open')"
		icon="open"
		target="_blank"
		class="k-view-preview-button"
	/>
</template>

<script>
/**
 * View header button to open the model's preview in a new tab
 * @displayName ViewPreviewButton
 * @since 5.0.0
 * @internal
 */
export default {
	computed: {
		isAvailable() {
			if (!this.link) {
				return false;
			}

			if (Object.hasOwn(this.permissions, "preview") === false) {
				return true;
			}

			return this.permissions.preview;
		},
		link() {
			return (
				this.$panel.view.props.model?.previewUrl ??
				this.$panel.view.props.preview?.url ??
				this.$panel.view.props.url
			);
		},
		permissions() {
			return this.$panel.view.props.permissions ?? {};
		}
	}
};
</script>
