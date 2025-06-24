<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-colorpicker-input
					name="colorpicker"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-colorpicker-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-colorpicker-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-colorpicker-input
					ref="input"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-colorpicker-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="colorpicker: false">
				<k-colorpicker-input
					:colorpicker="false"
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
