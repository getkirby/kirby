<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-box theme="notice" icon="alert">
			The structure field does not work without the backend yet
		</k-box>

		<k-lab-example label="Default">
			<k-structure-field
				label="Structure"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Required">
			<k-structure-field
				label="Structure"
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
			value: null
		};
	}
};
</script>
