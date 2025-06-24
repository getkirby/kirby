<template>
	<k-section
		:headline="label"
		:class="['k-info-section', $attrs.class]"
		:style="$attrs.style"
	>
		<k-box :html="true" :icon="icon" :text="text" :theme="theme" />
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";
export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	data() {
		return {
			icon: null,
			label: null,
			text: null,
			theme: null
		};
	},
	async mounted() {
		const response = await this.load();
		this.icon = response.icon;
		this.label = response.label;
		this.text = response.text;
		this.theme = response.theme ?? "info";
	}
};
</script>
