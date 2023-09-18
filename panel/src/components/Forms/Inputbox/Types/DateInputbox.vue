<template>
	<k-inputbox v-bind="$props" type="date">
		<k-date-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon slot="icon">
			<k-button
				:disabled="disabled"
				:icon="icon"
				:title="$t('date.select')"
				class="k-inputbox-icon-button"
				@click="$refs.calendar.toggle()"
			/>
			<k-dropdown-content ref="calendar" align-x="end">
				<k-calendar-input :value="value" :min="min" :max="max" @input="pick" />
			</k-dropdown-content>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/DateInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "calendar",
			type: String
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		pick(value) {
			this.$emit("input", value);
			this.$refs.calendar.close();
		}
	}
};
</script>

<style>
.k-date-inputbox .k-date-input {
	padding: var(--input-padding);
}
</style>
