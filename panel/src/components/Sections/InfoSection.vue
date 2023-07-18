<template>
	<k-section :headline="label" class="k-info-section">
		<k-box :html="true" :text="text" :theme="theme" />
	</k-section>
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
		this.theme = response.theme ?? "info";
	}
};
</script>
