<template>
	<k-notification
		:message="message"
		:type="type"
		class="k-dialog-notification"
		@close="$emit('close')"
	/>
</template>

<script>
import { props as Notification } from "@/components/Misc/Notification.vue";

export const props = {
	mixins: [Notification]
};

export default {
	mixins: [props]
};
</script>

<style>
.k-notification.k-dialog-notification {
	border-start-start-radius: var(--rounded);
	border-start-end-radius: var(--rounded);
	margin-top: -1px; /* to avoid a bleeding background with the border radius */
}
</style>
