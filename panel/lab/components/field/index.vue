<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-field input="a" label="Label">
				<k-input :value="value" id="a" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Required">
			<k-field input="b" :required="true" label="Label">
				<k-input id="b" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Buttons">
			<k-field
				input="c"
				:buttons="[{ icon: 'add', text: 'Add' }]"
				label="Label"
			>
				<k-input id="c" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Counter">
			<k-field
				:counter="{ min: 5, max: 10, count: value?.length }"
				input="d"
				label="Label"
			>
				<k-input id="d" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Options Slot">
			<k-field input="e" label="Label">
				<template #options>
					<k-button icon="add" size="xs" variant="filled">Add</k-button>
				</template>
				<k-input id="e" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Options Slot & excessive label">
			<k-field
				input="f"
				label="This is a very very long label that could push the options aside and break stuff"
			>
				<template #options>
					<k-button icon="add" size="xs" variant="filled">Add</k-button>
				</template>
				<k-input id="f" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
		<k-lab-example label="Help">
			<k-field input="g" help="This is some help" label="Label">
				<k-input id="g" :value="value" type="text" @input="value = $event" />
			</k-field>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null
		};
	}
};
</script>
