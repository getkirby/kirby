<template>
	<k-fieldset
		v-if="hasFields"
		:novalidate="novalidate"
		:fields="fields"
		:value="value"
		@input="$emit('input', $event)"
		@invalid="$emit('invalid', $event)"
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

export default {
	mixins: [props],
	computed: {
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		}
	}
};
</script>
