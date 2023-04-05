<template>
	<k-notification
		:message="message"
		:type="type"
		class="k-drawer-notification"
		@close="$emit('close')"
	/>
</template>

<script>
import { props as Notification } from "@/components/Misc/Notification.vue";

export const props = Notification;

export default {
	mixins: [props]
};
</script>

<style>
.k-notification.k-drawer-notification {
	padding: 0.625rem 1.5rem;
}
</style>
