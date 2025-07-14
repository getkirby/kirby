<template>
	<k-lab-form>
		<k-lab-examples>
			<k-lab-example label="Default">
				<k-stats-field :reports="reports" label="Stats" name="stats" />
			</k-lab-example>
		</k-lab-examples>
		<k-lab-examples>
			<k-lab-example label="Size (small)">
				<k-stats-field
					:reports="reports"
					label="Stats"
					name="stats"
					size="small"
				/>
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			reports: [
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
			]
		};
	}
};
</script>
