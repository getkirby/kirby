<template>
	<k-fieldset
		v-if="hasFields"
		:disabled="disabled"
		:fields="fields"
		:value="value"
		class="k-drawer-fields"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
	<k-box v-else theme="info">{{ empty }}</k-box>
</template>

<script>
export const props = {
	props: {
		disabled: Boolean,
		empty: {
			type: String,
			default: () => window.panel.t("drawer.fields.empty")
		},
		fields: Object,
		value: Object
	}
};

/**
 * @displayName DrawerFields
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["input", "submit"],
	computed: {
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		}
	}
};
</script>
