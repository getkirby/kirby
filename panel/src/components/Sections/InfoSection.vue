<template>
	<section class="k-info-section">
		<k-headline class="k-info-section-label">
			{{ label }}
		</k-headline>
		<k-box :html="true" :text="text" :theme="theme" />
	</section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";
export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	data() {
		return {
			label: null,
			text: null,
			theme: null
		};
	},
	async created() {
		const response = await this.load();
		this.label = response.label;
		this.text = response.text;
		this.theme = response.theme || "info";
	}
};
</script>

<style>
.k-info-section-label {
	margin-bottom: 0.5rem;
}
</style>
