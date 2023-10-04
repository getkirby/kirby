<template>
	<k-ui-options-input-examples
		:info="false"
		:value="value"
		type="select"
		@input="value = $event"
	>
		<template v-slot="{ options }">
			<k-ui-example label="Placeholder">
				<k-select-input
					:options="options"
					:value="value"
					placeholder="Placeholder text…"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Empty: false">
				<k-select-input
					:empty="false"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Multiple: true">
				<k-select-input
					:multiple="true"
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
			value: null,
		};
	},
};
</script>
