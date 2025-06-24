<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-checkboxes-field
					name="checkboxes"
					label="checkboxes"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-checkboxes-field
					:autofocus="true"
					label="checkboxes"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Options with info">
				<k-checkboxes-field
					label="checkboxes"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-checkboxes-field
					label="checkboxes"
					:options="optionsWithInfo"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Columns">
				<k-checkboxes-field
					:columns="3"
					label="checkboxes"
					:options="optionsWithInfo"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-checkboxes-field
					label="checkboxes"
					:options="optionsWithInfo"
					:disabled="true"
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
		}
	}
};
</script>
