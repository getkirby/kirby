<template>
	<k-lab-options-input-examples
		:value="value"
		type="checkboxes"
		@input="value = $event"
	>
		<template v-slot="{ options, optionsWithInfo }">
			<k-lab-example label="Min: 2">
				<k-checkboxes-input
					:min="2"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Max: 2">
				<k-checkboxes-input
					:max="2"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>
		</template>
	</k-lab-options-input-examples>
</template>

<script>
export default {
	data() {
		return {
			value: [],
		};
	},
};
</script>
