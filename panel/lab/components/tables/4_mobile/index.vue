<template>
	<k-lab-examples>
		<k-lab-example label="Horizontal">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th data-mobile="true">TH 1</th>
							<th data-mobile="true" style="width: 6rem">TH 2</th>
							<th>TH 3</th>
							<th>TH 4</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td data-mobile="true">TD 1</td>
							<td data-mobile="true" style="width: 6rem">TD 2</td>
							<td>TD 3</td>
							<td>TD 4</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		options() {
			return [
				{
					text: "Edit",
					icon: "edit"
				},
				{
					text: "Duplicate",
					icon: "copy"
				},
				"-",
				{
					text: "Delete",
					icon: "trash"
				}
			];
		}
	}
};
</script>
