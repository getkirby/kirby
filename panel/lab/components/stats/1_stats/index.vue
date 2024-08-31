<template>
	<k-lab-examples>
		<k-lab-example label="stats">
			<k-stats :reports="reportsWithoutTheme" />
		</k-lab-example>
		<k-lab-example label="themes">
			<k-stats :reports="reports" />
		</k-lab-example>
	</k-lab-examples>
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
					theme: "positive",
					icon: "preview"
				},
				{
					label: "Visitors",
					value: "3.500",
					info: "0%",
					theme: "info",
					icon: "users"
				},
				{
					label: "Searches",
					value: "1.250",
					info: "-10%",
					theme: "negative",
					icon: "search"
				}
			];
		},
		reportsWithoutTheme() {
			return this.reports.map((report) => ({
				...report,
				icon: null,
				theme: null
			}));
		}
	}
};
</script>
