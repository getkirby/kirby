<template>
	<form
		class="k-model-form"
		method="POST"
		@submit.prevent="$emit('submit', $event)"
	>
		<k-grid variant="columns">
			<k-column
				v-for="(column, columnIndex) in columns"
				:key="api + '-column-' + columnIndex"
				:width="column.width"
				:sticky="column.sticky"
			>
				<k-fieldset
					ref="fields"
					:disabled="lock && lock.state === 'lock'"
					:fields="fieldsWithAdditionalData(column.fields)"
					:value="content"
					:validate="true"
					@input="$emit('input', $event)"
					@submit="$emit('submit', $event)"
				/>
			</k-column>
		</k-grid>
	</form>
</template>

<script>
export default {
	props: {
		api: String,
		columns: Object,
		content: Object,
		diff: Object,
		lock: [Boolean, Object]
	},
	emits: ["input", "submit"],
	methods: {
		fieldsWithAdditionalData(fields) {
			const result = {};

			for (const name in fields) {
				result[name] = {
					...fields[name],
					endpoints: {
						field: this.api + "/fields/" + name,
						model: this.api
					},
					hasDiff: Object.hasOwn(this.diff ?? {}, name)
				};
			}

			return result;
		}
	}
};
</script>
