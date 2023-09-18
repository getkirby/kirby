<template>
	<k-field v-bind="$props" :input="_uid" :counter="false" class="k-list-field">
		<k-list-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/ListInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-list-inputbox>` for additional information.
 * @example <k-list-field :value="list" @input="list = $event" name="list" label="List" />
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
