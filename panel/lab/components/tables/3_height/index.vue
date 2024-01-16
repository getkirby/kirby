<template>
	<k-lab-examples>
		<k-grid style="--columns: 3; gap: var(--spacing-12)">
			<div>
				<k-lab-example label="Horizontal">
					<div class="k-table">
						<table>
							<tr>
								<td data-mobile="true">TD 1</td>
								<td data-mobile="true">TD 2</td>
							</tr>
						</table>
					</div>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="Vertical">
					<div class="k-table">
						<table style="table-layout: auto">
							<tbody>
								<tr v-for="i in 1" :key="i">
									<th data-mobile="true">Label {{ i }}</th>
									<td data-mobile="true">Value {{ i }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</k-lab-example>
			</div>
			<div>
				<k-lab-example label="Input">
					<k-input type="text" />
				</k-lab-example>
			</div>
		</k-grid>
	</k-lab-examples>
</template>
