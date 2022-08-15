<template>
	<td :data-align="column.align" :data-mobile="mobile">
		<template v-if="$helper.object.isEmpty(value) === false">
			<!-- Table cell type component -->
			<component
				:is="component"
				:column="column"
				:field="field"
				:row="row"
				:value="value"
				@input="$emit('input', $event)"
			/>
		</template>
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

			return "k-text-field-preview";
		},
		type() {
			return this.column.type || this.field?.type;
		}
	}
};
</script>
