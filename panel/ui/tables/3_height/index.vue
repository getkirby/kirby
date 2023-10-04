<template>
	<k-ui-examples>
		<k-grid style="--columns: 3; gap: var(--spacing-12)">
			<div>
				<k-ui-example label="Horizontal">
					<div class="k-table">
						<table>
							<tr>
								<td>TD 1</td>
								<td>TD 2</td>
							</tr>
						</table>
					</div>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="Vertical">
					<div class="k-table">
						<table style="table-layout: auto">
							<tbody>
								<tr v-for="i in 1" :key="i">
									<th>Label {{ i }}</th>
									<td>Value {{ i }}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="Input">
					<k-input type="text" />
				</k-ui-example>
			</div>
		</k-grid>
	</k-ui-examples>
</template>
