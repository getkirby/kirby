<template>
	<k-tabs :tab="tab" :tabs="withBadges" theme="notice" class="k-model-tabs" />
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		tab: String,
		tabs: {
			type: Array,
			default: () => []
		}
	},
	computed: {
		withBadges() {
			const changed = Object.keys(this.$store.getters["content/changes"]());

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
				tab.badge = fields.filter((field) =>
					changed.includes(field.toLowerCase())
				).length;

				return tab;
			});
		}
	}
};
</script>
