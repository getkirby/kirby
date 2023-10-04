<template>
	<k-ui-input-examples :value="value" type="toggle" @input="value = $event">
		<k-ui-example label="Text">
			<k-toggle-input
				:text="['no', 'yes']"
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

<style>
.k-ui-input-examples *:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
