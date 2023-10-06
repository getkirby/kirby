<template>
	<k-lab-examples>
		<k-lab-example label="Todo">
			<k-box theme="negative">
				The blocks field does not work without the backend yet
			</k-box>
		</k-lab-example>
		<k-lab-example label="Default">
			<k-blocks-field :label="label" :value="value" @input="value = $event" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			value: [],
		};
	},
	computed: {
		label() {
			return "Blocks";
		},
	},
};
</script>
