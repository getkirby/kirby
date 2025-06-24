<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<div class="k-table">
				<table style="table-layout: auto">
					<tbody>
						<tr v-for="i in 4" :key="i">
							<th>Label {{ i }}</th>
							<td>Value {{ i }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="TH Buttons">
			<div class="k-table">
				<table style="table-layout: auto">
					<tbody>
						<tr v-for="i in 4" :key="i">
							<th data-has-button="true">
								<button>Label {{ i }}</button>
							</th>
							<td>Value {{ i }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<div class="k-table" aria-disabled="true">
				<table style="table-layout: auto">
					<tbody>
						<tr v-for="i in 4" :key="i">
							<th>Label {{ i }}</th>
							<td>Value {{ i }}</td>
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
