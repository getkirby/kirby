<template>
	<k-field v-bind="$props" class="k-object-field">
		<!-- Remove button -->
		<template v-if="!disabled && hasFields" #options>
			<k-button
				v-if="isEmpty"
				icon="add"
				size="xs"
				variant="filled"
				@click="add"
			/>
			<k-button
				v-else
				icon="remove"
				size="xs"
				variant="filled"
				@click="remove"
			/>
		</template>

		<template v-if="hasFields">
			<table
				v-if="!isEmpty"
				:aria-disabled="disabled"
				class="k-table k-object-field-table"
			>
				<tbody>
					<template v-for="field in fields">
						<tr
							v-if="field.saveable && $helper.field.isVisible(field, value)"
							:key="field.name"
							@click="open(field.name)"
						>
							<th data-has-button data-mobile="true">
								<button type="button">{{ field.label }}</button>
							</th>
							<k-table-cell
								:column="field"
								:field="field"
								:mobile="true"
								:value="object[field.name]"
								@input="cell(field.name, $event)"
							/>
						</tr>
					</template>
				</tbody>
			</table>
			<k-empty v-else icon="box" @click="add">
				{{ empty ?? $t("field.object.empty") }}
			</k-empty>
		</template>
		<template v-else>
			<k-empty icon="box">{{ $t("fields.empty") }}</k-empty>
		</template>

		<!-- Validation -->
		<input
			type="checkbox"
			:checked="!isEmpty"
			:formnovalidate="novalidate"
			:required="required"
			class="input-hidden"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

export default {
	mixins: [Field, Input],
	props: {
		empty: String,
		fields: [Object, Array],
		value: [String, Object]
	},
	data() {
		return {
			object: {}
		};
	},
	computed: {
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		},
		isEmpty() {
			return (
				this.object === null || this.$helper.object.length(this.object) === 0
			);
		}
	},
	watch: {
		value: {
			handler(value) {
				this.object = this.valueToObject(value);
			},
			immediate: true
		}
	},
	methods: {
		add() {
			this.object = this.$helper.field.form(this.fields);
			this.save();
			this.open();
		},
		cell(name, value) {
			this.$set(this.object, name, value);
			this.save();
		},
		/**
		 * Config for the object form
		 * @returns {Object}
		 */
		form(autofocus) {
			const fields = this.$helper.field.subfields(this, this.fields);

			// set the autofocus to the matching field in the form
			if (autofocus) {
				for (const field in fields) {
					fields[field].autofocus = field === autofocus;
				}
			}

			return fields;
		},
		remove() {
			this.object = {};
			this.save();
		},
		open(field) {
			if (this.disabled) {
				return false;
			}

			this.$panel.drawer.open({
				component: "k-form-drawer",
				props: {
					breadcrumb: [],
					icon: "box",
					tab: "object",
					tabs: {
						object: {
							fields: this.form(field)
						}
					},
					title: this.label,
					value: this.object
				},
				on: {
					input: (value) => {
						// loop through all object keys and make
						// sure to make them reactive if they don't
						// exist yet
						for (const field in value) {
							this.$set(this.object, field, value[field]);
						}

						this.save();
					}
				}
			});
		},
		save() {
			this.$emit("input", this.object);
		},
		valueToObject(value) {
			return typeof value !== "object" ? {} : value;
		}
	}
};
</script>

<style>
.k-table.k-object-field-table {
	table-layout: auto;
}
.k-table.k-object-field-table tbody td {
	max-width: 0;
}
@container (max-width: 40rem) {
	.k-object-field {
		overflow: hidden;
	}
	.k-object-field-table.k-table tbody :where(th):is([data-mobile="true"]) {
		width: 1px !important;
		white-space: normal;
		word-break: normal;
	}
}
</style>
