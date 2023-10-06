<template>
	<k-lab-input-examples :value="value" type="toggle" @input="value = $event">
		<k-lab-example label="Text">
			<k-toggle-input
				:text="['no', 'yes']"
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

<style>
.k-lab-input-examples *:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
