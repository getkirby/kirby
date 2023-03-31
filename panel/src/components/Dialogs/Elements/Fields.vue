<template>
	<k-fieldset
		v-if="hasFields"
		:novalidate="novalidate"
		:fields="fields"
		:value="value"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
	<k-box v-else theme="negative">{{ empty }}</k-box>
</template>

<script>
export const props = {
	props: {
		empty: {
			type: String,
			default() {
				return "This form dialog has no fields";
			}
		},
		fields: {
			type: [Array, Object],
			default() {
				return [];
			}
		},
		novalidate: {
			type: Boolean,
			default: true
		},
		value: {
			type: Object,
			default() {
				return {};
			}
		}
	}
};

export default {
	mixins: [props],
	computed: {
		hasFields() {
			return Object.keys(this.fields || {}).length > 0;
		}
	}
};
</script>
