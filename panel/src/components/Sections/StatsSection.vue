<template>
	<section v-if="isLoading === false" class="k-stats-section">
		<header class="k-section-header">
			<k-headline>
				{{ headline }}
			</k-headline>
		</header>
		<k-stats v-if="reports.length > 0" :reports="reports" :size="size" />
		<k-empty v-else icon="chart"> {{ empty || $t("stats.empty") }}</k-empty>
	</section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
	mixins: [SectionMixin],
	data() {
		return {
			isLoading: true,
			headline: null,
			reports: null,
			size: null
		};
	},
	async created() {
		const section = await this.load();
		this.isLoading = false;
		this.headline = section.headline;
		this.reports = section.reports;
		this.size = section.size;
	},
	methods: {}
};
</script>
