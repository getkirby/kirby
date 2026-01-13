<template>
	<component
		:is="component"
		v-if="$helper.object.isEmpty(value) === false"
		:column="field"
		:field="field"
		:row="{}"
		:value="value"
	/>
	<p v-else class="k-text-field-preview">&nbsp;</p>
</template>

<script>
export default {
	props: {
		field: {
			type: Object
		},
		// eslint-disable-next-line vue/require-prop-types
		value: {
			default: ""
		}
	},
	computed: {
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
			return this.field.type;
		}
	}
};
</script>
