<template>
	<k-ui-options-input-examples
		:info="false"
		:value="value"
		type="multiselect"
		@input="value = $event"
	>
		<template v-slot="{ options }">
			<k-ui-example label="Min: 2">
				<k-multiselect-input
					:min="2"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Max: 2">
				<k-multiselect-input
					:max="2"
					:options="options"
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
