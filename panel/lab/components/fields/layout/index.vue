<template>
	<k-lab-examples>
		<k-box theme="notice" icon="alert">
			The blocks field does not work without the backend yet
		</k-box>

		<k-lab-example label="Default">
			<k-layout-field label="Layout" :value="value" @input="value = $event" />
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
