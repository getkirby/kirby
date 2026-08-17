<template>
	<k-box v-if="isEmpty" :text="empty" theme="info" />

	<form
		v-else
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
					@input="$emit('input', $event)"
					@submit="$emit('submit', $event)"
				/>
			</k-column>
		</k-grid>
	</form>
</template>

<script>
/**
 * Renders all columns of a model view tab as a single form.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	props: {
		api: String,
		columns: [Array, Object],
		content: Object,
		diff: Object,
		/**
		 * Text to show when the model has no columns at all
		 */
		empty: String,
		lock: [Boolean, Object]
	},
	emits: ["input", "submit"],
	computed: {
		disabled() {
			return this.lock?.state === "lock";
		},
		isEmpty() {
			return Object.keys(this.columns ?? {}).length === 0 && this.empty;
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
				result[name] = {
					...fields[name],
					endpoints: {
						model: this.api,
						field: this.api + "/fields/" + name
					},
					hasDiff: Object.hasOwn(this.diff ?? {}, name)
				};
			}

			return result;
		}
	}
};
</script>
