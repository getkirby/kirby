<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block type="fields" />
		</k-lab-example>
		<k-lab-example label="Tabs">
			<k-block type="fields" :fieldset="fieldsetWithTabs" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		fieldset() {
			return {
				name: "Test",
			};
		},
		fieldsetWithTabs() {
			return {
				...this.fieldset,
				tabs: {
					content: {
						name: "content",
						label: "Content",
					},
					settings: {
						name: "settings",
						label: "Settings",
					},
				},
			};
		},
	},
};
</script>
