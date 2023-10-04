<template>
	<k-ui-input-examples :value="value" type="number" @input="value = $event">
		<k-ui-example label="Step: 0.01">
			<k-number-input :step="0.01" :value="value" @input="value = $event" />
		</k-ui-example>
		<k-ui-example label="Min: 5">
			<k-number-input :min="5" :value="value" @input="value = $event" />
		</k-ui-example>
		<k-ui-example label="Max: 10">
			<k-number-input :max="10" :value="value" @input="value = $event" />
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
