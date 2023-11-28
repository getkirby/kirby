<template>
	<dl class="k-stats" :data-size="size">
		<k-stat v-for="(report, id) in reports" :key="id" v-bind="report" />
	</dl>
</template>

<script>
/**
 * Grid of reports which can be used to display multiple stats in a row  (e.g. as dashboard for a shop, analytics, etc.)
 *
 * @example <k-stats :reports="[{ value: 50, label: 'days' }, { value: 10, label: 'hours'}]" />
 */
export default {
	props: {
		/**
		 * List of stat reports. See `k-stat` for all options of each report.
		 */
		reports: {
			type: Array,
			default: () => []
		},
		/**
		 * Text size of the stat's value
		 * @values "small", "medium", "large", "huge"
		 */
		size: {
			type: String,
			default: "large"
		}
	},
	methods: {
		component(report) {
			if (this.target(report) !== null) {
				return "k-link";
			}

			return "div";
		},
		target(report) {
			if (report.link) {
				return report.link;
			}

			if (report.click) {
				return report.click;
			}

			if (report.dialog) {
				return () => this.$dialog(report.dialog);
			}

			return null;
		}
	}
};
</script>

<style>
.k-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
	grid-auto-rows: 1fr;
	grid-gap: var(--spacing-2px);
}

.k-stats[data-size="small"] {
	--stat-value-text-size: var(--text-md);
}
.k-stats[data-size="medium"] {
	--stat-value-text-size: var(--text-xl);
}
.k-stats[data-size="large"] {
	--stat-value-text-size: var(--text-2xl);
}
.k-stats[data-size="huge"] {
	--stat-value-text-size: var(--text-3xl);
}
</style>
