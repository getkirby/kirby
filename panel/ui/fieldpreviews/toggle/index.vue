<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			:value="value"
			label="Default"
			type="toggle"
			@input="value = $event"
		/>
		<k-ui-field-preview-example
			:field="{ text: ['no', 'yes'] }"
			:value="value"
			label="Text"
			type="toggle"
			@input="value = $event"
		/>
		<k-ui-field-preview-example
			label="Empty"
			type="toggle"
			@input="value = $event"
		/>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			value: false,
		};
	},
};
</script>
