<template>
	<k-lab-input-examples :value="value" type="time" @input="value = $event">
		<k-lab-example label="Min: 12:00">
			<k-time-input :value="value" min="12:00" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Max: 18:00">
			<k-time-input :value="value" max="18:00" @input="value = $event" />
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
