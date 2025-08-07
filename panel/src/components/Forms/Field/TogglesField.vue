<template>
	<k-field
		v-bind="$props"
		:class="['k-toggles-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-empty
			v-if="!options?.length"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-input
			v-else
			ref="input"
			:class="{ grow }"
			v-bind="$props"
			type="toggles"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TogglesInput } from "../Input/TogglesInput.vue";

export default {
	mixins: [Field, Input, TogglesInput],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onInput(value) {
			this.$emit("input", value);
		}
	}
};
</script>
