<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-lab-example label="No fields">
			<k-object-field label="Object" />
		</k-lab-example>

		<k-lab-example label="Default">
			<k-object-field
				:fields="{
					name: {
						label: 'Name',
						name: 'name',
						saveable: true,
						type: 'text'
					}
				}"
				:value="value"
				label="Object"
				@input="value = $event"
			/>
		</k-lab-example>

		<k-lab-example label="Required">
			<k-object-field
				:fields="{
					name: {
						label: 'Name',
						name: 'name',
						saveable: true,
						type: 'text'
					}
				}"
				:required="true"
				:value="value"
				label="Object"
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
	}
};
</script>
