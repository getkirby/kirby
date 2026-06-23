<template>
	<k-section
		v-if="isLoading === false"
		:headline="headline"
		class="k-stats-section"
	>
		<k-stats :reports="reports" :size="size" />
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

/**
 * @deprecated 6.0.0 Use `k-stats-field` instead
 */
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
