<template>
	<k-lab-input-examples
		:placeholder="false"
		:value="value"
		type="colorpicker"
		@input="value = $event"
	>
		<k-lab-example label="Alpha: false">
			<k-colorpicker-input
				:alpha="false"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Options">
			<k-colorpicker-input
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Format: hsl">
			<k-colorpicker-input
				:options="options"
				:value="value"
				format="hsl"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Format: rgb">
			<k-colorpicker-input
				:options="options"
				:value="value"
				format="rgb"
				@input="value = $event"
			/>
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
	computed: {
		options() {
			return [
				{ value: "#F8B195", text: "Sunny rays" },
				{ value: "#F67280", text: "First-love blush" },
				{ value: "#C06C84", text: "Cherry blossom" },
				{ value: "#6C5B7B", text: "Morning gloom" },
				{ value: "#355C7D", text: "Midnight rain" },
			];
		},
	},
};
</script>

<style>
.k-lab-example fieldset:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
