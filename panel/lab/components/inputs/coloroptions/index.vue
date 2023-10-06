<template>
	<k-lab-options-input-examples
		:info="false"
		:options="options"
		:value="value"
		type="coloroptions"
		@input="value = $event"
	>
		<k-lab-example label="Format: RGB">
			<k-coloroptions-input
				:options="options"
				:value="value"
				name="coloroptions"
				format="rgb"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Format: HSL">
			<k-coloroptions-input
				:options="options"
				:value="value"
				name="coloroptions"
				format="hsl"
				@input="value = $event"
			/>
		</k-lab-example>
	</k-lab-options-input-examples>
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
				{ value: "#fff", text: "White" },
				{ value: "#000", text: "Black" },
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
.k-lab-examples fieldset:invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
