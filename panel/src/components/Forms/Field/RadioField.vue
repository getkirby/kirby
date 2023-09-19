<template>
	<k-field v-bind="$props" class="k-radio-field">
		<k-radio-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/RadioInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-radio-inputbox>` for additional information.
 *
 * @example <k-radio-field :options="options" :value="value" label="Options" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
