<template>
	<k-notification
		v-if="$panel.notification.context === 'drawer'"
		class="k-drawer-notification"
	/>
</template>

<style>
.k-notification.k-drawer-notification {
	padding: 0.625rem 1.5rem;
}
</style>
