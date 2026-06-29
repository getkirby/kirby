<template>
	<k-text v-if="text" :html="text" />
	<k-box v-else theme="info">{{ empty }}</k-box>
</template>

<script>
/**
 * @since 4.0.0
 */
export const props = {
	props: {
		/**
		 * Empty state message if no text is defined
		 */
		empty: {
			type: String,
			default: () => window.panel.t("dialog.text.empty")
		},
		text: {
			type: String
		}
	}
};

/**
 * @displayName DialogText
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [props]
};
</script>
