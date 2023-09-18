<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-text-field"
	>
		<template #options>
			<slot name="options" />
		</template>
		<k-text-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/TextInputbox.vue";
import Counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>` and `<k-text-inputbox>`
 * for additional information.
 * @example <k-text-field :value="text" @input="text = $event" name="text" label="Boring text" />
 */
export default {
	mixins: [FieldProps, InputboxProps, Counter],
	inheritAttrs: false
};
</script>
