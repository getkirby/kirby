<template>
	<k-inputbox v-bind="$props" type="time">
		<k-time-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon v-if="times" slot="icon">
			<k-button
				:disabled="disabled"
				:icon="icon"
				:title="$t('time.select')"
				class="k-inputbox-icon-button"
				@click="$refs.times.toggle()"
			/>
			<k-dropdown-content ref="times" align-x="end">
				<k-timeoption-input :display="display" :value="value" @input="pick" />
			</k-dropdown-content>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/TimeInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		/**
		 * Icon used for the times dropdown
		 */
		icon: {
			default: "clock",
			type: String
		},
		/**
		 * Deactivate the times dropdown or not
		 */
		times: {
			type: Boolean,
			default: true
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		/**
		 * Handles the input event from the times dropdown
		 * @param {string} value
		 */
		pick(value) {
			this.$emit("input", value);
			this.$refs.times?.close();
		}
	}
};
</script>

<style>
.k-time-inputbox .k-time-input {
	padding: var(--input-padding);
}
</style>
