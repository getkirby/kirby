<template>
	<k-section
		v-if="isLoading === false"
		:headline="headline"
		class="k-stats-section"
	>
		<k-stats v-if="reports.length > 0" :reports="reports" :size="size" />
		<k-empty v-else icon="chart"> {{ $t("stats.empty") }}</k-empty>
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
	mixins: [SectionMixin],
	data() {
		return {
			headline: null,
			isLoading: true,
			reports: null,
			size: null
		};
	},
	async mounted() {
		const section = await this.load();
		this.isLoading = false;
		this.headline = section.headline;
		this.reports = section.reports;
		this.size = section.size;
	},
	methods: {}
};
</script>
