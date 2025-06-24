<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-coloroptions-input
					name="coloroptions"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-coloroptions-input
					:autofocus="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-coloroptions-input
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-coloroptions-input
					ref="input"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-coloroptions-input
					:disabled="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

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
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			value: null
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
				{ value: "#355C7D", text: "Midnight rain" }
			];
		}
	}
};
</script>
