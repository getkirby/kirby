<template>
	<k-button
		v-if="notification && notification.type !== 'error'"
		:icon="notification.icon"
		:text="notification.message"
		:theme="notification.theme"
		variant="filled"
		class="k-panel-notification"
		@click="notification.close()"
	/>
</template>

<script>
/**
 * @displayName PanelNotifications
 * @since 6.0.0
 */
export default {
	computed: {
		notification() {
			if (
				this.$panel.notification.context === "view" &&
				!this.$panel.notification.isFatal
			) {
				return this.$panel.notification;
			}

			return null;
		}
	}
};
</script>

<style>
.k-panel-notification {
	--button-height: var(--height-md);
	--button-color-icon: var(--theme-color-900);
	--button-color-text: var(--theme-color-900);
	border: 1px solid var(--theme-color-500);
	position: fixed;
	inset-block-end: var(--menu-padding);
	inset-inline-end: var(--menu-padding);
	box-shadow: var(--dropdown-shadow);
	z-index: var(--z-notification);
}
</style>
