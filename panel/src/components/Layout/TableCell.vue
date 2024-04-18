<template>
	<td
		:class="['k-table-cell', $attrs.class]"
		:data-align="column.align"
		:data-column-id="id"
		:data-mobile="mobile"
		:style="$attrs.style"
	>
		<!-- Table cell type component -->
		<component
			:is="component"
			v-if="$helper.object.isEmpty(value) === false"
			:column="column"
			:field="field"
			:row="row"
			:value="value"
			@input="$emit('input', $event)"
		/>
	</td>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		/**
		 * Column options
		 */
		column: Object,
		/**
		 * Optional corresponding field options
		 */
		field: Object,
		id: String,
		/**
		 * Keep cell on mobile
		 */
		mobile: {
			type: Boolean,
			default: false
		},
		/**
		 * Current row
		 */
		row: Object,
		// eslint-disable-next-line vue/require-prop-types
		value: {
			default: ""
		}
	},
	emits: ["input"],
	computed: {
		/**
		 * Returns the component name (if exists) for
		 * - field preview
		 * - cell type
		 * @returns {string|false}
		 */
		component() {
			if (this.$helper.isComponent(`k-${this.type}-field-preview`)) {
				return `k-${this.type}-field-preview`;
			}

			if (this.$helper.isComponent(`k-table-${this.type}-cell`)) {
				return `k-table-${this.type}-cell`;
			}

			if (Array.isArray(this.value)) {
				return "k-array-field-preview";
			}

			if (typeof this.value === "object") {
				return "k-object-field-preview";
			}

			return "k-text-field-preview";
		},
		type() {
			return this.column.type ?? this.field?.type;
		}
	}
};
</script>

<style>
.k-table .k-table-cell {
	padding: 0;
}
</style>
