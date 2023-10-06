<template>
	<k-lab-input-examples :value="value" type="textarea" @input="value = $event">
		<k-lab-example label="Minlength: 5">
			<k-textarea-input :minlength="5" :value="value" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Maxlength: 10">
			<k-textarea-input
				:maxlength="10"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
	</k-lab-input-examples>
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
