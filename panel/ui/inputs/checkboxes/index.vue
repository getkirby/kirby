<template>
	<k-ui-options-input-examples
		:value="value"
		type="checkboxes"
		@input="value = $event"
	>
		<template v-slot="{ options, optionsWithInfo }">
			<k-ui-example label="Min: 2">
				<k-checkboxes-input
					:min="2"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Max: 2">
				<k-checkboxes-input
					:max="2"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-ui-example>
		</template>
	</k-ui-options-input-examples>
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
