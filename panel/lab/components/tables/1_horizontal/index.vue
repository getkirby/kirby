<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th>TH 1</th>
							<th>TH 2</th>
							<th>TH 3</th>
							<th>TH 4</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td>TD 1</td>
							<td>TD 2</td>
							<td>TD 3</td>
							<td>TD 4</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Text alignment">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th data-align="left">Left</th>
							<th data-align="center">Center</th>
							<th data-align="right">Right</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td data-align="left">Left</td>
							<td data-align="center">Center</td>
							<td data-align="right">Right</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="TH Buttons">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th data-has-button="true"><button>Name</button></th>
							<th data-has-button="true"><button>Date</button></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td>Kirby</td>
							<td>2023-09-25</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Options">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th>Name</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td>Kirby</td>
							<td>2023-09-25</td>
							<td class="k-table-options-column">
								<k-options-dropdown :options="options" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Index">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-index-column">#</th>
							<th>Name</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td class="k-table-index-column">
								<span class="k-table-index">{{ i }}</span>
							</td>
							<td>Kirby</td>
							<td>2023-09-25</td>
							<td class="k-table-options-column">
								<k-options-dropdown :options="options" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Index with sort handle">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-index-column">#</th>
							<th>Name</th>
							<th>Email</th>
							<th>URL</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<k-draggable
						:handle="true"
						:options="{
							fallbackClass: 'k-table-row-fallback',
							ghostClass: 'k-table-row-ghost'
						}"
						element="tbody"
					>
						<tr v-for="i in 2" :key="i" class="k-table-sortable-row">
							<td class="k-table-index-column" data-sortable="true">
								<span class="k-table-index">{{ i }}</span>
								<k-sort-handle class="k-table-sort-handle" />
							</td>
							<td>Kirby</td>
							<td>mail@getkirby.com</td>
							<td>https://getkirby.com</td>
							<td>2023-09-25</td>
							<td class="k-table-options-column">
								<k-options-dropdown :options="options" />
							</td>
						</tr>
					</k-draggable>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<div aria-disabled="true" class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-index-column">#</th>
							<th>Name</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td class="k-table-index-column">
								<span class="k-table-index">{{ i }}</span>
							</td>
							<td>Kirby</td>
							<td>2023-09-25</td>
							<td class="k-table-options-column">
								<k-options-dropdown :disabled="true" :options="options" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
		<k-lab-example label="Pagination">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-index-column">#</th>
							<th>Name</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="i in 2" :key="i">
							<td class="k-table-index-column">
								<span class="k-table-index">{{ i }}</span>
							</td>
							<td>Kirby</td>
							<td>2023-09-25</td>
							<td class="k-table-options-column">
								<k-options-dropdown :disabled="true" :options="options" />
							</td>
						</tr>
					</tbody>
				</table>
				<k-pagination
					class="k-table-pagination"
					:details="true"
					:limit="2"
					:page="1"
					:total="10"
				/>
			</div>
		</k-lab-example>
		<k-lab-example label="Empty">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-index-column">#</th>
							<th>Name</th>
							<th>Date</th>
							<th class="k-table-options-column"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="k-table-empty" colspan="4">No rows yet</td>
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
