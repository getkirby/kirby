<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-toggles-field
				:label="label"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Autofocus">
			<k-toggles-field
				:autofocus="true"
				:label="label"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Required">
			<k-toggles-field
				:label="label"
				:options="options"
				:required="true"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Help">
			<k-toggles-field
				:label="label"
				:options="options"
				:value="value"
				help="This is some help text"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-toggles-field
				:disabled="true"
				:label="label"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="No labels">
			<k-toggles-field
				:label="label"
				:labels="false"
				:options="options"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Grow: true">
			<k-toggles-field
				:grow="true"
				:label="label"
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
			value: null,
		};
	},
	computed: {
		label() {
			return "Toggles";
		},
		options() {
			return [
				{ text: "Option A", value: "a", icon: "text-left" },
				{ text: "Option B", value: "b", icon: "text-center" },
				{ text: "Option C", value: "c", icon: "text-right" },
			];
		},
	},
};
</script>
