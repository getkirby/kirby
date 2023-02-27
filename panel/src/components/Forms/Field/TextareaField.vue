<template>
	<k-field
		v-bind="$props"
		:input="id"
		:counter="counterOptions"
		class="k-textarea-field"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="textarea"
			theme="field"
			v-on="{
				...$listeners,
				paste: onPaste
			}"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TextareaInput } from "../Input/TextareaInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-textarea-input>`
 * for additional information.
 * @example <k-textarea-field :value="text" @input="text = $event" name="text" label="Text" />
 */
export default {
	mixins: [Field, Input, TextareaInput, counter],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		async onPaste(e) {
			if (e.clipboardData?.getData("text/html")) {
				e.preventDefault();

				// pass html or plain text to the paste endpoint to convert it to blocks
				const response = await this.$api.post(this.endpoints.field + "/paste", {
					html: e.clipboardData?.getData("text/html")
				});

				this.$emit("input", response.markdown);
			}
		}
	}
};
</script>
