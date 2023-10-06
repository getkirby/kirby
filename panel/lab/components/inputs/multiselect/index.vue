<template>
	<k-lab-options-input-examples
		:info="false"
		:value="value"
		type="multiselect"
		@input="value = $event"
	>
		<template v-slot="{ options }">
			<k-lab-example label="Min: 2">
				<k-multiselect-input
					:min="2"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Max: 2">
				<k-multiselect-input
					:max="2"
					:options="options"
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
