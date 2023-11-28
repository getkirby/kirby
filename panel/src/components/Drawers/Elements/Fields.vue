<template>
	<k-fieldset
		v-if="hasFields"
		:novalidate="novalidate"
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
		empty: {
			type: String,
			default: () => window.panel.$t("drawer.fields.empty")
		},
		fields: Object,
		novalidate: {
			type: Boolean,
			default: true
		},
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
