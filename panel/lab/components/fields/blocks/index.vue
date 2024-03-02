<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-box theme="notice" icon="alert">
			The blocks field does not work without the backend yet
		</k-box>

		<k-lab-example label="Default">
			<k-blocks-field label="Blocks" :value="value" @input="value = $event" />
		</k-lab-example>

		<k-lab-example label="Required">
			<k-blocks-field
				label="Blocks"
				:required="true"
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
			value: []
		};
	}
};
</script>
