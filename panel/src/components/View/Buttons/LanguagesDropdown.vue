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
		 * If translations other than the currently-viewed one
		 * have any unsaved changes
		 */
		hasChanges: Boolean,
		options: String
	},
	computed: {
		changesBadge() {
			if (this.hasChanges || this.$panel.content.hasChanges) {
				return {
					theme: "notice"
				};
			}

			return;
		}
	}
};
</script>
