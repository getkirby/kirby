<template>
	<k-lab-examples>
		<k-lab-example label="Fieldset">
			<k-fieldset
				:fields="{
					name: {
						label: 'Name',
						name: 'name',
						type: 'text'
					},
					email: {
						label: 'Email',
						name: 'email',
						type: 'email'
					}
				}"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-fieldset
				:disabled="true"
				:fields="{
					name: {
						label: 'Name',
						name: 'name',
						type: 'text'
					},
					email: {
						label: 'Email',
						name: 'email',
						type: 'email'
					}
				}"
				:value="value"
				@input="value = $event"
			/>
		</k-lab-example>
		<k-lab-example :code="false" label="Output">
			<k-code>{{ value }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			value: {},
		};
	},
};
</script>
