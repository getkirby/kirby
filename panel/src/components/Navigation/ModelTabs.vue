<template>
	<k-tabs :tab="tab" :tabs="withBadges" theme="notice" class="k-model-tabs" />
</template>

<script>
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
				let fields = [];
				Object.values(tab.columns).forEach((column) => {
					Object.values(column.sections).forEach((section) => {
						if (section.type === "fields") {
							Object.keys(section.fields).forEach((field) => {
								fields.push(field);
							});
						}
					});
				});

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

<style>
.k-model-tabs {
	margin-bottom: var(--spacing-8);
}
</style>
