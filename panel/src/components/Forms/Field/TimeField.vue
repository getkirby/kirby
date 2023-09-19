<template>
	<k-field v-bind="$props" :input="_uid" class="k-time-field">
		<k-time-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/TimeInputbox.vue";

/**
 * Form field to handle a time value.
 *
 * Have a look at `<k-field>` and `<k-time-inputbox>` for additional information.
 *
 * @example <k-time-field :value="value" label="Time" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
