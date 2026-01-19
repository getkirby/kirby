<template>
	<k-field
		v-bind="$props"
		:class="['k-time-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="time"
			@input="$emit('input', $event ?? '')"
			@submit="$emit('submit')"
		>
			<template v-if="times" #icon>
				<k-button
					:disabled="disabled"
					:icon="icon ?? 'clock'"
					:title="$t('time.select')"
					class="k-input-icon-button"
					@click="$refs.times.toggle()"
				/>
				<k-dropdown ref="times" align-x="end">
					<k-timeoptions-input
						:display="display"
						:value="value"
						@input="select"
					/>
				</k-dropdown>
			</template>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TimeInput } from "../Input/TimeInput.vue";

/**
 * Form field to handle a time value.
 *
 * Have a look at `<k-field>`, `<k-input>`
 * and `<k-time-input>` for additional information.
 *
 * @example <k-time-field :value="time" @input="time = $event" name="time" label="Time" />
 */
export default {
	mixins: [Field, Input, TimeInput],
	inheritAttrs: false,
	props: {
		/**
		 * Icon used for the input (and times dropdown)
		 */
		icon: {
			type: String,
			default: "clock"
		},
		/**
		 * Deactivate the times dropdown or not
		 */
		times: {
			type: Boolean,
			default: true
		}
	},
	emits: ["input", "submit"],
	methods: {
		/**
		 * Focuses the input element
		 * @public
		 */
		focus() {
			this.$refs.input.focus();
		},
		/**
		 * Handles the input event from the times dropdown
		 * @param {string} value
		 */
		select(value) {
			this.$emit("input", value);
			this.$refs.times?.close();
		}
	}
};
</script>
