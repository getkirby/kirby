<template>
	<k-field v-bind="$props" :input="_uid" class="k-tel-field">
		<k-tel-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/TelInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-tel-inputbox>` for additional information.
 *
 * @example <k-tel-field :value="value" label="Phone number" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
