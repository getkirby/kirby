<template>
	<k-field v-bind="$props" class="k-object-field">
		<table class="k-table k-object-field-table">
			<tbody>
				<tr v-for="field in fields" :key="field.name" @click="open(field.name)">
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
			</tbody>
		</table>
		<k-form-drawer ref="drawer" v-bind="drawer" @input="onDrawerInput" />
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

export default {
	mixins: [Field, Input],
	props: {
		fields: Object,
		value: Object
	},
	data() {
		return {
			autofocus: null,
			object: this.value
		};
	},
	watch: {
		value() {
			this.object = this.value;
		}
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
		}
	},
	methods: {
		onCellInput(name, value) {
			this.$set(this.object, name, value);
			this.$emit("input", this.object);
		},
		onDrawerInput(value) {
			this.object = value;
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
.k-table.k-object-field-table tbody th {
	cursor: pointer;
}
</style>
