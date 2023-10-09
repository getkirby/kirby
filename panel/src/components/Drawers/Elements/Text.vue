<template>
	<k-text v-if="text" :html="text" />
	<k-box v-else theme="info">{{ empty }}</k-box>
</template>

<script>
export const props = {
	props: {
		/**
		 * Empty state message if no text is defined
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

export default {
	mixins: [props]
};
</script>
