<template>
	<k-lab-input-examples :value="value" type="date" @input="value = $event">
		<k-lab-example label="Min: 2023-01-01">
			<k-date-input :value="value" min="2023-01-01" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Max: 2023-12-31">
			<k-date-input :value="value" max="2023-12-31" @input="value = $event" />
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
