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

export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
