<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-lab-example label="Default">
			<k-toggles-field
				label="Toggles"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Autofocus">
			<k-toggles-field
				:autofocus="true"
				label="Toggles"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Required">
			<k-toggles-field
				label="Toggles"
				:options="options"
				:required="true"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Help">
			<k-toggles-field
				label="Toggles"
				:options="options"
				:value="value"
				help="This is some help text"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Disabled">
			<k-toggles-field
				:disabled="true"
				label="Toggles"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Labels: true">
			<k-toggles-field
				label="Toggles"
				:labels="true"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Grow: true">
			<k-toggles-field
				:grow="true"
				label="Toggles"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
	</k-lab-examples>
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
				{ text: "Option A", value: "a", icon: "text-left" },
				{ text: "Option B", value: "b", icon: "text-center" },
				{ text: "Option C", value: "c", icon: "text-right" }
			];
		}
	}
};
</script>
