<template>
	<k-ui-input-examples :value="value" type="textarea" @input="value = $event">
		<k-ui-example label="Minlength: 5">
			<k-textarea-input :minlength="5" :value="value" @input="value = $event" />
		</k-ui-example>
		<k-ui-example label="Maxlength: 10">
			<k-textarea-input
				:maxlength="10"
				:value="value"
				@input="value = $event"
			/>
		</k-ui-example>
	</k-ui-input-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
};
</script>
