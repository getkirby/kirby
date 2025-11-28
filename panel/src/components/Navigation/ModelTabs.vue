<template>
	<k-tabs :tab="tab" :tabs="withBadges" theme="notice" class="k-model-tabs" />
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		diff: Object,
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
				// collect all fields per tab
				const fields = [];

				for (const column in tab.columns) {
					for (const section in tab.columns[column].sections) {
						if (tab.columns[column].sections[section].type === "fields") {
							for (const field in tab.columns[column].sections[section]
								.fields) {
								fields.push(field);
							}
						}
					}
				}

				// get count of changed fields in this tab
				const changesInTab = fields.filter((field) =>
					changes.includes(field.toLowerCase())
				).length;

				if (changesInTab > 0) {
					tab.badge = {
						text: changesInTab
					};
				}

				return tab;
			});
		}
	}
};
</script>
