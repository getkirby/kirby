<template>
	<k-field v-bind="$props" class="k-object-field">
		<!-- Remove button -->
		<template v-if="!disabled && hasFields" #options>
			<k-button
				v-if="isEmpty"
				icon="add"
				size="xs"
				variant="filled"
				@click="onAdd"
			/>
			<k-button
				v-else
				icon="remove"
				size="xs"
				variant="filled"
				@click="onRemove"
			/>
		</template>

		<template v-if="hasFields">
			<table
				v-if="!isEmpty"
				:data-invalid="isInvalid"
				class="k-table k-object-field-table"
			>
				<tbody>
					<template v-for="field in fields">
						<tr
							v-if="field.saveable && $helper.field.isVisible(field, value)"
							:key="field.name"
							@click="open(field.name)"
						>
							<th data-mobile="true">
								<button type="button">{{ field.label }}</button>
							</th>
							<k-table-cell
								:column="field"
								:field="field"
								:mobile="true"
								:value="object[field.name]"
								@input="onCellInput(field.name, $event)"
							/>
						</tr>
					</template>
				</tbody>
			</table>
			<k-empty v-else :data-invalid="isInvalid" icon="box" @click="onAdd">
				{{ empty || $t("field.object.empty") }}
			</k-empty>
		</template>
		<template v-else>
			<k-empty icon="box">{{ $t("fields.empty") }}</k-empty>
		</template>
	</k-field>
</template>

<script>
import { set } from "vue";
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

export default {
	mixins: [Field, Input],
	props: {
		empty: String,
		fields: Object,
		value: [String, Object]
	},
	data() {
		return {
			object: this.valueToObject(this.value)
		};
	},
	computed: {
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		},
		isEmpty() {
			return this.object === null;
		},
		isInvalid() {
			return this.required === true && this.isEmpty;
		}
	},
	watch: {
		value(value) {
			this.object = this.valueToObject(value);
		}
	},
	methods: {
		onAdd() {
			this.object = this.$helper.field.form(this.fields);
			this.$emit("input", this.object);
			this.open();
		},
		onCellInput(name, value) {
			set(this.object, name, value);
			this.$emit("input", this.object);
		},
		onDrawerInput(value) {
			this.object = value;
			this.$emit("input", this.object);
		},
		onRemove() {
			this.object = null;
			this.$emit("input", this.object);
		},
		// TODO: field is not yet used to pre-focus correct field
		// eslint-disable-next-line no-unused-vars
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
							fields: this.$helper.field.subfields(this, this.fields)
						}
					},
					title: this.label,
					value: this.object
				},
				on: {
					input: this.onDrawerInput.bind(this)
				}
			});
		},
		valueToObject(value) {
			return typeof value !== "object" ? null : value;
		}
	}
};
</script>

<style>
.k-table.k-object-field-table {
	table-layout: auto;
}
.k-table.k-object-field-table tbody td,
.k-table.k-object-field-table tbody th,
.k-table.k-object-field-table tbody th button {
	cursor: pointer;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-table.k-object-field-table tbody td {
	max-width: 0;
}
</style>
