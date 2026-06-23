<template>
	<form
		class="k-model-form"
		method="POST"
		@submit.prevent="$emit('submit', $event)"
	>
		<k-grid variant="columns">
			<k-column
				v-for="(column, columnKey) in resolvedColumns"
				:key="api + '-column-' + columnKey"
				:width="column.width"
				:sticky="column.sticky"
			>
				<k-fieldset
					ref="fieldsets"
					:disabled="disabled"
					:fields="column.fields"
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
		columns: [Array, Object],
		content: Object,
		diff: Object,
		lock: [Boolean, Object]
	},
	emits: ["input", "submit"],
	computed: {
		disabled() {
			return this.lock?.state === "lock";
		},
		resolvedColumns() {
			const columns = {};

			for (const key in this.columns) {
				columns[key] = {
					...this.columns[key],
					fields: this.fieldsWithAdditionalData(this.columns[key].fields)
				};
			}

			return columns;
		}
	},
	methods: {
		fieldsWithAdditionalData(fields) {
			const result = {};

			for (const name in fields) {
				const field = fields[name];

				// section fields talk to the section endpoint,
				// all other fields to the field endpoint
				// TODO: drop this once we use field endpoints
				const endpoints =
					field.type === "section"
						? { model: this.api, section: this.api + "/sections/" + name }
						: { model: this.api, field: this.api + "/fields/" + name };

				result[name] = {
					...field,
					endpoints,
					hasDiff: Object.hasOwn(this.diff ?? {}, name)
				};
			}

			return result;
		}
	}
};
</script>
