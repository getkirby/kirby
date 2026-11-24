<template>
	<k-notification
		v-if="$panel.notification.context === 'drawer'"
		class="k-drawer-notification"
	/>
</template>

<script>
/**
 * @displayName DrawerNotification
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 */
export default {};
</script>

<style>
.k-notification.k-drawer-notification {
	padding: 0.625rem 1.5rem;
}
</style>
