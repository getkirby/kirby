<template>
	<k-field v-bind="$props" class="k-toggles-field">
		<k-toggles-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/TogglesInputbox.vue";

export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
