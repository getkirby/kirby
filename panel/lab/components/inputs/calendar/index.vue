<template>
	<k-lab-input-examples :value="value" type="calendar" @input="value = $event">
		<k-lab-example label="Min (2023-02-15)">
			<k-calendar-input
				:value="value"
				min="2023-02-15"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Max (2023-09-15)">
			<k-calendar-input
				:value="value"
				max="2023-09-15"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Min (2023-09-02) Max (2023-09-15)">
			<k-calendar-input
				:value="value"
				min="2023-09-02"
				max="2023-09-15"
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

<style>
.k-lab-example fieldset:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
