<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-password-field"
	>
		<template #options>
			<slot name="options" />
		</template>
		<k-password-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/PasswordInputbox.vue";
import Counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>` and `<k-password-input>` for additional information.
 * @example <k-password-field :value="password" @input="password = $event" name="password" label="Password" />
 */
export default {
	mixins: [FieldProps, InputboxProps, Counter],
	inheritAttrs: false
};
</script>
