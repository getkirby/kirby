<template>
	<k-lab-example :label="label">
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
							<component
								:is="`k-${type}-field-preview`"
								:column="column"
								:field="field"
								:value="value"
								@input="$emit('input', $event)"
							/>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</k-lab-example>
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
		// eslint-disable-next-line vue/require-prop-types
		value: {}
	},
	emits: ["input"]
};
</script>

<style>
.k-lab-input-examples *:not([type="checkbox"], [type="radio"]):invalid {
	outline: 2px solid var(--color-red-600) !important;
}
</style>
