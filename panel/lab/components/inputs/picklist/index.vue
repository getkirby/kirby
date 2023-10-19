<template>
	<k-lab-options-input-examples
		:value="value"
		type="picklist"
		@input="value = $event"
	>
		<template v-slot="{ options }">
			<k-lab-example label="Multiple: false">
				<k-picklist-input :multiple="false" :options="options" value="c" />
			</k-lab-example>

			<k-lab-example label="Create">
				<k-picklist-input :create="true" :options="options" />
			</k-lab-example>

			<k-lab-example label="Max: 2">
				<k-picklist-input :create="true" :options="options" :max="2" />
			</k-lab-example>

			<k-lab-example label="Search: false">
				<k-picklist-input :options="options" :search="false" />
			</k-lab-example>
		</template>
	</k-lab-options-input-examples>
</template>

<script>
export default {
	data() {
		return {
			value: []
		};
	}
};
</script>
