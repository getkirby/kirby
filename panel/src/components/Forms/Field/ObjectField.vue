<template>
	<k-field v-bind="$props" class="k-object-field">
		<!-- Remove button -->
		<template v-if="!disabled" #options>
			<k-button v-if="isEmpty" icon="add" @click="onAdd" />
			<k-button v-else icon="remove" @click="onRemove" />
		</template>

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

		<k-form-drawer ref="drawer" v-bind="drawer" @input="onDrawerInput" />
	</k-field>
</template>

<script>
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
		drawer() {
			return {
				icon: "box",
				tab: "object",
				tabs: {
					object: {
						fields: this.$helper.field.subfields(this, this.fields)
					}
				},
				title: this.label,
				value: this.object
			};
		},
		isEmpty() {
			if (!this.object) {
				return true;
			}

			if (this.object && Object.keys(this.object).length === 0) {
				return true;
			}

			return false;
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
			this.object = {};

			for (const fieldName in this.fields) {
				const field = this.fields[fieldName];

				if (field.default) {
					this.object[fieldName] = this.$helper.clone(field.default);
				}
			}

			this.$emit("input", this.object);
			this.open();
		},
		onCellInput(name, value) {
			this.$set(this.object, name, value);
			this.$emit("input", this.object);
		},
		onDrawerInput(value) {
			this.object = value;
			this.$emit("input", this.object);
		},
		onRemove() {
			this.object = {};
			this.$emit("input", this.object);
		},
		open(field) {
			if (this.disabled) {
				return false;
			}

			this.$refs.drawer.open(null, field);
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
