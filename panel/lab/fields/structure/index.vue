<template>
	<k-lab-examples>
		<k-lab-example label="Todo">
			<k-box theme="negative">
				The structure field does not work without the backend yet
			</k-box>
		</k-lab-example>
		<k-lab-example label="Default">
			<k-structure-field
				:label="label"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
	</k-lab-examples>
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
