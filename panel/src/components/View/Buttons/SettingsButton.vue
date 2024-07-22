<template>
	<k-view-button
		:disabled="$panel.content.isLocked"
		:title="$t('settings')"
		icon="cog"
		class="k-view-settings-button k-view-options"
		:options="hasDropdown ? dropdown : null"
		@click="$emit('action', 'settings')"
	/>
</template>

<script>
/**
 * View header button to open the model's settings dropdown
 * @displayName ViewSettingsButton
 * @since 5.0.0
 * @internal
 */
export default {
	inheritAttrs: false,
	emits: ["action", "click"],
	computed: {
		hasDropdown() {
			return Boolean(this.dropdown);
		},
		dropdown() {
			return this.model?.link;
		},
		model() {
			return this.$panel.view.props.model;
		}
	}
};
</script>
