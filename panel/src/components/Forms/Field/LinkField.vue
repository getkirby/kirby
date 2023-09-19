<template>
	<k-field v-bind="$props" :input="_uid" class="k-link-field">
		<k-link-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/LinkInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-link-inputbox>` for additional information.
 *
 * @example <k-link-field :value="value" label="Link" @input="value = $event" />
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
