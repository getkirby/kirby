<template>
	<k-lab-input-examples
		:placeholder="false"
		:value="value"
		type="range"
		@input="value = $event"
	>
		<k-lab-example label="Step: 0.01">
			<k-range-input :step="0.01" :value="value" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Min: 5">
			<k-range-input :min="5" :value="value" @input="value = $event" />
		</k-lab-example>
		<k-lab-example label="Max: 10">
			<k-range-input :max="10" :value="value" @input="value = $event" />
		</k-lab-example>
	</k-lab-input-examples>
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
