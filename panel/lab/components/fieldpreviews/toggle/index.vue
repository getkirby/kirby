<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-toggle-field-preview :value="value" @input="value = $event" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Text">
			<k-lab-table-cell>
				<!-- @code -->
				<k-toggle-field-preview
					:field="{ text: ['no', 'yes'] }"
					:value="value"
					@input="value = $event"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-toggle-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Disabled">
			<k-lab-table-cell>
				<!-- @code -->
				<k-toggle-field-preview :field="{ disabled: true }" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			value: false
		};
	}
};
</script>
