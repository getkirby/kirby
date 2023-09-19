<template>
	<k-field v-bind="$props" :input="_uid" class="k-number-field">
		<k-number-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/NumberInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-number-inputbox>` for additional information.
 *
 * @example <k-number-field :value="value" label="Number" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
