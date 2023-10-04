<template>
	<k-ui-field-examples :value="value" type="color" @input="value = $event">
		<k-ui-example label="Format: rgb">
			<k-color-field
				:label="label"
				:value="value"
				format="rgb"
				@input="value = $event"
			/>
		</k-ui-example>
		<k-ui-example label="Format: hsl">
			<k-color-field
				:label="label"
				:value="value"
				format="hsl"
				@input="value = $event"
			/>
		</k-ui-example>
		<k-ui-example label="Picker with options">
			<k-color-field
				:label="label"
				:options="options"
				:value="value"
				before="Before"
				after="After"
				@input="value = $event"
			/>
		</k-ui-example>
		<k-ui-example label="Mode: options">
			<k-color-field
				:label="label"
				:options="options"
				:value="value"
				mode="options"
				@input="value = $event"
			/>
		</k-ui-example>
		<k-ui-example label="Mode: input">
			<k-color-field
				:label="label"
				:options="options"
				:value="value"
				mode="input"
				@input="value = $event"
			/>
		</k-ui-example>
	</k-ui-field-examples>
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
			return "Color";
		},
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
