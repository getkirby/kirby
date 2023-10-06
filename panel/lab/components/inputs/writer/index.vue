<template>
	<k-lab-input-examples :value="value" type="writer" @input="value = $event">
		<k-lab-example label="Minlength: 5">
			<k-writer-input :minlength="5" :value="value" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Maxlength: 10">
			<k-writer-input :maxlength="10" :value="value" @input="value = $event" />
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
