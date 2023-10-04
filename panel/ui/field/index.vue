<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-field input="a" label="Label">
				<k-input :value="value" id="a" type="text" @input="value = $event" />
			</k-field>
		</k-ui-example>
		<k-ui-example label="Required">
			<k-field input="b" :required="true" label="Label">
				<k-input :value="value" id="b" type="text" @input="value = $event" />
			</k-field>
		</k-ui-example>
		<k-ui-example label="Counter">
			<k-field
				:counter="{ min: 5, max: 10, count: value?.length }"
				input="c"
				label="Label"
			>
				<k-input :value="value" id="c" type="text" @input="value = $event" />
			</k-field>
		</k-ui-example>
		<k-ui-example label="Options Slot">
			<k-field input="d" label="Label">
				<k-button icon="add" size="xs" slot="options" variant="filled">
					Add
				</k-button>
				<k-input :value="value" id="d" type="text" @input="value = $event" />
			</k-field>
		</k-ui-example>
		<k-ui-example label="Help">
			<k-field input="e" help="This is some help" label="Label">
				<k-input :value="value" id="e" type="text" @input="value = $event" />
			</k-field>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
};
</script>
