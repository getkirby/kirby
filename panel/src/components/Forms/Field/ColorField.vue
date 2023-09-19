<template>
	<k-field v-bind="$props" :input="_uid" class="k-color-field">
		<k-color-inputbox
			v-bind="$props"
			:id="_uid"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/ColorInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-color-inputbox>` for additional information.
 * @example <k-color-field :value="color" @input="color = $event" name="color" label="Color" />
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
