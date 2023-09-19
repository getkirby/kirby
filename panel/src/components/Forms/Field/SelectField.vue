<template>
	<k-field v-bind="$props" :input="_uid" class="k-select-field">
		<k-select-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/SelectInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-select-inputbox>` for additional information.
 *
 * @example <k-select-field :options="options" :value="value" label="Select" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
