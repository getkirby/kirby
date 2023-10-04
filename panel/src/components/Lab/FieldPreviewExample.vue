<template>
	<k-ui-example :label="label">
		<div class="k-table">
			<table>
				<thead>
					<tr>
						<th data-mobile="true">
							{{ $helper.string.ucfirst(type) }} Field Preview
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="k-table-cell">
							<component :is="`k-${type}-field-preview`" :column="column" :field="field" :value="value" @input="$emit('input', $event)" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		column: {
			default: () => {
				return {};
			},
			type: Object
		},
		field: {
			default: () => {
				return {};
			},
			type: Object
		},
		label: String,
		type: String,
		value: {},
	},
};
</script>

<style>
.k-ui-input-examples *:not([type="checkbox"], [type="radio"]):invalid {
	outline: 2px solid var(--color-red-600) !important;
}
</style>
