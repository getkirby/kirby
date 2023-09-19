<template>
	<k-field v-bind="$props" :input="_uid" class="k-email-field">
		<k-email-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/EmailInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-email-inputbox>` for additional information.
 *
 * @example <k-email-field :value="value" label="Email" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
