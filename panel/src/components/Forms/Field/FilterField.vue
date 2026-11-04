<template>
	<k-field
		v-bind="$props"
		:input="false"
		:class="['k-filter-field', $attrs.class]"
		:style="$attrs.style"
		@click.stop
	>
		<!-- Options -->
		<template v-if="!disabled && hasFields" #options>
			<k-button-group layout="collapsed">
				<k-button
					v-if="more"
					:autofocus="autofocus"
					:responsive="true"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					class="input-focus"
					@click="add()"
				/>
				<k-button
					icon="dots"
					variant="filled"
					size="xs"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown ref="options" :options="options" align-x="end" />
			</k-button-group>
		</template>

		<!-- No fields -->
		<k-empty v-if="!hasFields" icon="filter">
			{{ $t("fields.empty") }}
		</k-empty>

		<k-validator v-else v-bind="{ min, max, required }" :count="rows.length">
			<!-- Empty State -->
			<k-empty
				v-if="rows.length === 0"
				icon="filter"
				v-on="!disabled ? { click: () => add() } : {}"
			>
				{{ empty ?? $t("field.filter.empty") }}
			</k-empty>

			<!-- Conditions -->
			<div
				v-else
				:data-operators="hasOperatorColumn"
				class="k-filter-field-items"
			>
				<div
					v-for="(row, index) in rows"
					:key="row.id"
					class="k-filter-field-item"
				>
					<!-- unknown fields are shown as empty selection -->
					<k-select-field
						:ref="'condition-' + index + '-field'"
						:disabled="disabled"
						:label="false"
						:options="fieldOptions"
						:placeholder="$t('field.filter.field')"
						:required="true"
						:value="fitsField(row.condition) ? row.condition.field : ''"
						class="k-filter-field-item-field"
						@input="onFieldInput(index, $event)"
					/>
					<template v-if="hasOperatorColumn">
						<!-- a single fitting operator is shown as plain text -->
						<span
							v-if="isFixedOperator(row.condition)"
							class="k-filter-field-item-operator k-filter-field-item-operator-fixed"
						>
							{{ operatorsFor(row.condition.field)[0].text }}
						</span>
						<!-- operators that don't fit are shown as empty selection -->
						<k-select-field
							v-else
							:disabled="disabled"
							:label="false"
							:options="operatorsFor(row.condition.field)"
							:placeholder="$t('field.filter.operator')"
							:required="true"
							:value="fitsOperator(row.condition) ? row.condition.operator : ''"
							class="k-filter-field-item-operator"
							@input="onInput(index, 'operator', $event)"
						/>
					</template>
					<component
						:is="`k-${target(row.condition.field).type}-field`"
						v-bind="target(row.condition.field)"
						:counter="false"
						:disabled="disabled"
						:help="undefined"
						:label="false"
						:value="row.condition.value"
						class="k-filter-field-item-value"
						@input="onInput(index, 'value', $event)"
					/>
					<k-button
						v-if="!disabled"
						:title="$t('remove')"
						class="k-filter-field-item-remove"
						icon="trash"
						size="sm"
						@click="remove(index)"
					/>
				</div>
			</div>
		</k-validator>

		<footer v-if="hasFields && more" class="k-filter-field-footer">
			<k-button
				:title="$t('add')"
				icon="add"
				size="xs"
				variant="filled"
				@click="add()"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

/**
 * Allows editors to define a list of conditions,
 * each consisting of a field, an operator and a value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	mixins: [Field, Input],
	inheritAttrs: false,
	props: {
		/**
		 * The text, that is shown when the field has no conditions.
		 */
		empty: String,
		/**
		 * Fields that can be matched in a condition
		 */
		fields: {
			type: [Array, Object],
			default: () => ({})
		},
		/**
		 * Upper limit of conditions allowed
		 */
		max: Number,
		/**
		 * Lower limit of conditions required
		 */
		min: Number,
		/**
		 * Available operators for each field as `{ value, text }` options,
		 * keyed by field name
		 */
		operators: {
			type: [Array, Object],
			default: () => ({})
		},
		value: {
			type: Array,
			default: () => []
		}
	},
	emits: ["input"],
	data() {
		return {
			rows: []
		};
	},
	computed: {
		/**
		 * Returns the options for the field select.
		 * Fields without any fitting operators are skipped.
		 * @returns {array}
		 */
		fieldOptions() {
			return Object.values(this.fields)
				.filter((field) => this.operatorsFor(field.name).length > 0)
				.map((field) => ({
					value: field.name,
					text: field.label ?? field.name
				}));
		},
		/**
		 * Returns the operator option, if all fields share
		 * the same single operator. Otherwise returns `null`.
		 * @returns {object|null}
		 */
		fixedOperator() {
			const operators = this.fieldOptions.map((field) =>
				this.operatorsFor(field.value)
			);

			if (
				operators.length === 0 ||
				operators.some((options) => options.length !== 1) === true
			) {
				return null;
			}

			const first = operators[0][0];

			if (operators.every(([option]) => option.value === first.value)) {
				return first;
			}

			return null;
		},
		/**
		 * Returns whether any fields can be matched
		 * @returns {boolean}
		 */
		hasFields() {
			return this.fieldOptions.length > 0;
		},
		/**
		 * Returns whether the operator column is shown.
		 * It is hidden if all fields share the same single operator,
		 * unless a condition has an operator that doesn't fit.
		 * @returns {boolean}
		 */
		hasOperatorColumn() {
			if (this.fixedOperator === null) {
				return true;
			}

			return this.rows.some(
				(row) => this.fitsOperator(row.condition) === false
			);
		},
		/**
		 * Returns if new conditions can be added
		 * @returns {boolean}
		 */
		more() {
			if (this.disabled === true) {
				return false;
			}

			if (this.max && this.rows.length >= this.max) {
				return false;
			}

			return true;
		},
		options() {
			return [
				{
					disabled: this.rows.length === 0 || this.disabled,
					icon: "trash",
					text: this.$t("delete.all"),
					click: () => this.removeAll()
				}
			];
		},
		values() {
			return this.rows.map((row) => row.condition);
		}
	},
	watch: {
		value: {
			handler(conditions) {
				conditions ??= [];

				// Prevent unnecessary re-creation of rows that causes focus loss
				// when typing in input fields by comparing actual values
				if (JSON.stringify(conditions) === JSON.stringify(this.values)) {
					return;
				}

				this.rows = conditions.map((condition) => ({
					id: this.$helper.uuid(),
					condition: { ...condition }
				}));
			},
			immediate: true
		}
	},
	methods: {
		async add(index = null) {
			if (this.more === false || this.hasFields === false) {
				return;
			}

			const field = this.fieldOptions[0]?.value;

			index ??= this.rows.length;
			this.rows.splice(index, 0, {
				id: this.$helper.uuid(),
				condition: {
					field: field,
					operator: this.operatorsFor(field)[0]?.value ?? "",
					value: this.defaultValue(field)
				}
			});

			this.save();

			await this.$nextTick();
			this.focus(index);
		},
		/**
		 * Returns the default value for the given field
		 * @param {string} name
		 * @returns {any}
		 */
		defaultValue(name) {
			const field = this.target(name);
			return this.$helper.field.form({ field })?.field ?? "";
		},
		/**
		 * Returns whether the field of the condition can be selected
		 * @param {object} condition
		 * @returns {boolean}
		 */
		fitsField(condition) {
			return this.fieldOptions.some(
				(option) => option.value === condition.field
			);
		},
		/**
		 * Returns whether the operator of the condition fits its field
		 * @param {object} condition
		 * @returns {boolean}
		 */
		fitsOperator(condition) {
			return this.operatorsFor(condition.field).some(
				(option) => option.value === condition.operator
			);
		},
		focus(index) {
			this.$refs["condition-" + index + "-field"]?.[0]?.focus?.();
		},
		/**
		 * Returns whether the condition's field only offers
		 * a single operator, which is already selected
		 * @param {object} condition
		 * @returns {boolean}
		 */
		isFixedOperator(condition) {
			return (
				this.operatorsFor(condition.field).length === 1 &&
				this.fitsOperator(condition) === true
			);
		},
		onFieldInput(index, name) {
			const condition = this.rows[index].condition;

			// reset the value when switching to a different field
			// as the type of value might not be compatible
			if (condition.field !== name) {
				condition.value = this.defaultValue(name);
			}

			// keep the operator if it also fits the new field,
			// otherwise fall back to the first fitting operator
			const operators = this.operatorsFor(name);

			if (operators.some((o) => o.value === condition.operator) === false) {
				condition.operator = operators[0]?.value ?? "";
			}

			condition.field = name;
			this.save();
		},
		onInput(index, key, value) {
			this.rows[index].condition[key] = value;
			this.save();
		},
		/**
		 * Returns the operator options for the given field
		 * @param {string} name
		 * @returns {array}
		 */
		operatorsFor(name) {
			return this.operators[name] ?? [];
		},
		remove(index) {
			if (this.disabled === true) {
				return;
			}

			this.rows.splice(index, 1);
			this.save();
			this.focus(Math.max(index - 1, 0));
		},
		removeAll() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.filter.delete.confirm.all")
				},
				on: {
					submit: () => {
						this.rows = [];
						this.save();
						this.$panel.dialog.close();
					}
				}
			});
		},
		save() {
			this.$emit("input", this.values);
		},
		/**
		 * Returns the field definition for the value input
		 * of a condition. Falls back to a text field for unknown fields.
		 * @param {string} name
		 * @returns {object}
		 */
		target(name) {
			const field = Object.values(this.fields).find((f) => f.name === name);
			return field ?? { type: "text" };
		}
	}
};
</script>

<style>
.k-filter-field-items {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.k-filter-field-item {
	display: grid;
	grid-template-columns: 1fr 1fr 2fr auto;
	align-items: stretch;
	min-height: var(--input-height);
	background: var(--input-color-back);
	border-radius: var(--rounded);
}

.k-filter-field-items[data-operators="false"] .k-filter-field-item {
	grid-template-columns: 1fr 2fr auto;
}

.k-filter-field:not([data-disabled="true"]) .k-filter-field-item {
	--input-color-border: transparent;
	box-shadow: var(--shadow);
}

.k-filter-field-item > .k-field {
	min-width: 0;
}

.k-filter-field-item > .k-field:not(:first-child) {
	border-inline-start: 1px solid var(--panel-color-back);
}

.k-filter-field-item-operator-fixed {
	display: flex;
	align-items: center;
	min-width: 0;
	padding-inline: var(--input-padding);
	font-size: var(--input-font-size);
	color: var(--color-text-dimmed);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	border-inline-start: 1px solid var(--panel-color-back);
}

.k-filter-field-item-remove.k-button {
	--button-height: 100%;
	--button-width: var(--input-height);
	border-inline-start: 1px solid var(--panel-color-back);
}

@container (max-width: 30rem) {
	.k-filter-field-item {
		grid-template-columns: 1fr 1fr auto;
	}

	.k-filter-field-items[data-operators="false"] .k-filter-field-item {
		grid-template-columns: 1fr auto;
	}

	/* the value spans all columns except the last one */
	.k-filter-field-item-value {
		grid-column: 1 / -2;
		grid-row: 2;
		border-inline-start: 0 !important;
		border-top: 1px solid var(--panel-color-back);
	}

	.k-filter-field-item-remove.k-button {
		grid-row: 1 / 3;
		grid-column: -2 / -1;
	}
}

.k-filter-field-footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
