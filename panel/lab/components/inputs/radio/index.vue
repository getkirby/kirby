<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-radio-input
					name="radio"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-radio-input
					:autofocus="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-radio-input
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Options with info">
				<k-radio-input
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Options with info & icon">
				<k-radio-input
					:options="optionsWithInfoAndIcon"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-radio-input
					ref="input"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-radio-input
					:disabled="true"
					:options="optionsWithInfo"
					:value="value"
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
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		},
		optionsWithInfo() {
			return [
				{ text: "Option A", value: "a", info: "This is some info text" },
				{ text: "Option B", value: "b", info: "This is some info text" },
				{ text: "Option C", value: "c", info: "This is some info text" }
			];
		},
		optionsWithInfoAndIcon() {
			return [
				{
					text: "Option A",
					value: "a",
					info: "This is some info text",
					icon: "heart"
				},
				{
					text: "Option B",
					value: "b",
					info: "This is some info text"
				},
				{
					text: "Option C",
					value: "c",
					info: "This is some info text",
					icon: "star"
				}
			];
		}
	}
};
</script>
