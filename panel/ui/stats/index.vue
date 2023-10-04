<template>
	<k-ui-examples>
		<k-ui-example label="stats">
			<k-stats :reports="reportsWithoutTheme" />
		</k-ui-example>
		<k-ui-example label="themes">
			<k-stats :reports="reports" />
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		reports() {
			return [
				{
					label: "Views",
					value: "12.250",
					info: "+120%",
					theme: "positive"
				},
				{
					label: "Visitors",
					value: "3.500",
					info: "0%",
					theme: "info"
				},
				{
					label: "Searches",
					value: "1.250",
					info: "-10%",
					theme: "negative"
				}
			];
		},
		reportsWithoutTheme() {
			return this.$helper.clone(this.reports).map(report => {
				report.theme = null;
				return report;
			})
		}
	}
};
</script>
