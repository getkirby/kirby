<template>
	<k-button
		v-if="isAvailable"
		:link="link"
		:title="$t('open')"
		icon="open"
		size="sm"
		target="_blank"
		variant="filled"
		class="k-view-preview-button"
	/>
</template>

<script>
/**
 * View header button to open the model's preview in a new tab
 * @since 5.0.0
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
