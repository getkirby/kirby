<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			label="Default"
			type="date"
			value="2023-09-25"
		/>
		<k-lab-field-preview-example
			:field="{ display: 'DD.MM.YYYY' }"
			label="Display"
			type="date"
			value="2023-09-25"
		/>
		<k-lab-field-preview-example
			:field="{ time: { display: 'HH:mm' } }"
			label="Time"
			type="date"
			value="2023-09-25 12:12:12"
		/>
		<k-lab-field-preview-example label="Empty" type="date" />
	</k-lab-examples>
</template>
