<template>
	<k-lab-examples>
		<k-lab-example>
			<k-file-preview-thumb> 👋 </k-file-preview-thumb>
		</k-lab-example>
		<k-lab-example label="Options">
			<k-file-preview-thumb :options="options"> 👋 </k-file-preview-thumb>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		options() {
			return [{ text: "Website", icon: "open", link: "https://getkirby.com" }];
		}
	}
};
</script>

<style>
.k-lab-example-canvas .k-file-preview-thumb-column {
	max-height: 15rem;
}
</style>
