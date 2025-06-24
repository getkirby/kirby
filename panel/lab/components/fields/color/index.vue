<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-color-field
					name="color"
					label="color"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-color-field
					:autofocus="true"
					label="color"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-color-field
					label="color"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-color-field
					label="color"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-color-field
					label="color"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-color-field
					label="color"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-color-field
					label="color"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-color-field
					:disabled="true"
					label="color"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Format: rgb">
				<k-color-field
					label="color"
					:value="value"
					format="rgb"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Format: hsl">
				<k-color-field
					label="color"
					:value="value"
					format="hsl"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Picker with options">
				<k-color-field
					label="color"
					:options="options"
					:value="value"
					before="Before"
					after="After"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Mode: options">
				<k-color-field
					label="color"
					:options="options"
					:value="value"
					mode="options"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Mode: input">
				<k-color-field
					label="color"
					:options="options"
					:value="value"
					mode="input"
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
