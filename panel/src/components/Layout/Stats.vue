<template>
	<dl v-if="reports.length > 0" class="k-stats" :data-size="size">
		<k-stat v-for="(report, id) in reports" :key="id" v-bind="report" />
	</dl>
	<k-empty v-else icon="chart">{{ $t("stats.empty") }}</k-empty>
</template>

<script>
export const props = {
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
	}
};

/**
 * Grid of reports which can be used to display multiple stats in a row  (e.g. as dashboard for a shop, analytics, etc.)
 *
 * @example <k-stats :reports="[{ value: 50, label: 'days' }, { value: 10, label: 'hours'}]" />
 */
export default {
	mixins: [props]
};
</script>

<style>
.k-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
	grid-auto-rows: 1fr;
	grid-gap: 2px;
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
