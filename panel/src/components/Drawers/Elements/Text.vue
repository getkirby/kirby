<template>
	<k-text v-if="text" :html="text" />
	<k-box v-else theme="info">{{ empty }}</k-box>
</template>

<script>
/**
 * @displayName DrawerText
 */
export const props = {
	props: {
		/**
		 * Empty state message if no text is defined
		 * @since 4.0.0
		 */
		empty: {
			type: String,
			default: () => window.panel.$t("drawer.text.empty")
		},
		text: {
			type: String
		}
	}
};

/**
 * @displayName DrawerText
 */
export default {
	mixins: [props]
};
</script>
