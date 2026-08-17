<template>
	<k-tabs :tab="tab" :tabs="withBadges" theme="notice" class="k-model-tabs" />
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default {
	props: {
		diff: {
			type: Object,
			default: () => ({})
		},
		tab: String,
		tabs: {
			type: Array,
			default: () => []
		}
	},
	computed: {
		withBadges() {
			const changes = Object.keys(this.diff);

			return this.tabs.map((tab) => {
				// all field names of the tab
				const fields = tab.fields ?? [];

				// get count of changed fields in this tab
				const changesInTab = fields.filter((field) =>
					changes.includes(field.toLowerCase())
				).length;

				return {
					...tab,
					badge: changesInTab > 0 ? { text: changesInTab } : undefined
				};
			});
		}
	}
};
</script>
