<template>
	<label class="k-checkbox-input" @click.stop>
		<k-choice
			ref="input"
			:id="id"
			:checked="value"
			:disabled="disabled"
			type="checkbox"
			@input="onChange"
		/>
		<!-- eslint-disable-next-line vue/no-v-html -->
		<span class="k-checkbox-input-label" v-html="label" />
	</label>
</template>

<script>
import { autofocus, disabled, id, label, required } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

/**
 *
 * @example <k-input :value="checkbox" @input="checkbox = $event" type="checkbox" />
 */
export default {
	mixins: [autofocus, disabled, id, label, required],
	inheritAttrs: false,
	props: {
		value: Boolean
	},
	watch: {
		value() {
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onChange(checked) {
			/**
			 * The input event is triggered when the value changes.
			 * @event input
			 * @property {boolean} checked
			 */
			this.$emit("input", checked);
		},
		onInvalid() {
			/**
			 * The invalid event is triggered when the input validation fails. This can be used to react on errors immediately.
			 * @event invalid
			 */
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		select() {
			this.focus();
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true
			}
		};
	}
};
</script>

<style>
.k-checkbox-input {
	display: flex;
	align-items: center;
	position: relative;
	cursor: pointer;
}
</style>
