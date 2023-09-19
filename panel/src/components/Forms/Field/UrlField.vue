<template>
	<k-field v-bind="$props" :input="_uid" class="k-url-field">
		<k-url-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/UrlInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-url-inputbox>` for additional information.
 *
 * @example <k-url-field :value="value" label="Url" @input="value = $event" />
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
