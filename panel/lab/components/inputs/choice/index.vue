<template>
	<k-lab-input-examples
		:placeholder="false"
		:value="value"
		type="choice"
		@input="value = $event"
	>
		<k-lab-example label="Label">
			<k-choice-input :checked="value" label="Option" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Label & Info">
			<k-choice-input
				:checked="value"
				info="This is some info text"
				label="Option"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Type: radio">
			<k-choice-input
				:checked="value"
				info="This is some info text"
				label="Option"
				type="radio"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Variant: toggle">
			<k-choice-input
				:checked="value"
				info="This is some info text"
				label="Option"
				variant="toggle"
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
.k-lab-examples *:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
