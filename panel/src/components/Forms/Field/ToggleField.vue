<template>
	<k-field v-bind="$props" :input="_uid" class="k-toggle-field">
		<k-toggle-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/ToggleInputbox.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-toggle-inputbox>` for additional information.
 *
 * @example <k-toggle-field :value="value" label="Toggle" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
