<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-multiselect-field
					name="multiselect"
					label="multiselect"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-multiselect-field
					:autofocus="true"
					label="multiselect"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-multiselect-field
					label="multiselect"
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-multiselect-field
					label="multiselect"
					:options="options"
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
			value: []
		};
	},
	computed: {
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		}
	}
};
</script>
