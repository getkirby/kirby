<template>
	<k-notification
		v-if="$panel.notification.context === 'dialog'"
		class="k-dialog-notification"
	/>
</template>

<style>
.k-dialog .k-notification {
	padding-block: 0.325rem;
	border-start-start-radius: var(--rounded);
	border-start-end-radius: var(--rounded);
	margin-top: -1px; /* to avoid a bleeding background with the border radius */
}
</style>
