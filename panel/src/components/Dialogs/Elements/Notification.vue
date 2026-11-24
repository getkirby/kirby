<template>
	<k-notification
		v-if="$panel.notification.context === 'dialog'"
		class="k-dialog-notification"
	/>
</template>

<script>
/**
 * Displays notification from `panel.notification` in a dialog
 *
 * @displayName DialogNotification
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 */
export default {};
</script>

<style>
.k-dialog .k-notification {
	padding-block: 0.325rem;
	border-start-start-radius: var(--dialog-rounded);
	border-start-end-radius: var(--dialog-rounded);
	margin-top: -1px; /* to avoid a bleeding background with the border radius */
}
</style>
