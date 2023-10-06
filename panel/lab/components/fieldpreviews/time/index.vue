<template>
	<k-lab-examples>
		<k-lab-field-preview-example label="Default" type="time" value="22:33:00" />
		<k-lab-field-preview-example
			:field="{ display: 'HH:mm:ss' }"
			label="Display"
			type="time"
			value="22:33:00"
		/>
		<k-lab-field-preview-example label="Empty" type="time" />
	</k-lab-examples>
</template>
