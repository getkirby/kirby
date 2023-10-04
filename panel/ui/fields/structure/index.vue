<template>
	<k-ui-examples>
		<k-ui-example label="Todo">
			<k-box theme="negative">
				The structure field does not work without the backend yet
			</k-box>
		</k-ui-example>
		<k-ui-example label="Default">
			<k-structure-field
				:label="label"
				:value="value"
				@input="value = $event"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
	computed: {
		label() {
			return "Structure";
		},
	},
};
</script>
