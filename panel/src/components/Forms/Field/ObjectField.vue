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
						v-if="$helper.field.isVisible(field, value)"
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
							:value="value[field.name]"
							@input="onCellInput(field.name, $event)"
						/>
					</tr>
				</template>
			</tbody>
		</table>
		<k-empty v-else :data-invalid="isInvalid" icon="box" @click="onAdd">
			{{ empty || $t("field.object.empty") }}
		</k-empty>

		<k-form-drawer
			ref="drawer"
			v-bind="drawer"
			@input="onDrawerInput"
			@invalid="onErrors"
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
		fields: Object,
		value: Object
	},
	data() {
		return {
			object: this.value,
			errors: []
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
			if (!this.value) {
				return true;
			}

			if (this.value && Object.keys(this.value).length === 0) {
				return true;
			}

			return false;
		},
		isInvalid() {
			// if field itself is required and empty
			if (this.required === true && this.isEmpty) {
				return true;
			}

			// if subfields are required but empty
			for (const field in this.fields) {
				if (
					this.fields[field].required === true &&
					this.$helper.object.isEmpty(this.value[field]) === true
				) {
					return true;
				}
			}

			// if subfields has any error
			if (this.errors.length > 0) {
				return true;
			}

			return false;
		}
	},
	watch: {
		value(value) {
			this.object = value;
			this.errors = [];
		}
	},
	methods: {
		onAdd() {
			this.object = {};

			for (const fieldName in this.fields) {
				const field = this.fields[fieldName];
				const value = field.default ? this.$helper.clone(field.default) : null;

				this.object[fieldName] = value;
			}

			console.log(this.object);

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
		onErrors(fields) {
			this.errors = Object.keys(fields).filter(
				(field) => fields[field].$invalid
			);
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
