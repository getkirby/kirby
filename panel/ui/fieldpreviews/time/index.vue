<template>
	<k-ui-examples>
		<k-ui-field-preview-example label="Default" type="time" value="22:33:00" />
		<k-ui-field-preview-example
			:field="{ display: 'HH:mm:ss' }"
			label="Display"
			type="time"
			value="22:33:00"
		/>
		<k-ui-field-preview-example label="Empty" type="time" />
	</k-ui-examples>
</template>
