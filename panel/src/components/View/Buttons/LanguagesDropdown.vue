<template>
	<k-view-button v-bind="$props" :badge="changesBadge" />
</template>

<script>
import { props as ButtonProps } from "@/components/Navigation/Button.vue";

/**
 * View header button to switch between content languages
 * @displayName LanguagesViewButton
 * @since 4.0.0
 * @internal
 */
export default {
	mixins: [ButtonProps],
	props: {
		/**
		 * Number of translations with unsaved changes
		 * other than the currently viewed one
		 */
		changes: Number,
		options: String
	},
	computed: {
		changesBadge() {
			let changes = this.changes ?? 0;

			if (this.$panel.content.hasChanges) {
				changes++;
			}

			if (changes === 0) {
				return;
			}

			return {
				theme: "notice",
				text: changes
			};
		}
	}
};
</script>
