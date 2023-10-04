<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-block type="fields" />
		</k-ui-example>
		<k-ui-example label="Tabs">
			<k-block type="fields" :fieldset="fieldsetWithTabs" />
		</k-ui-example>
	</k-ui-examples>
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
