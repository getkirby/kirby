<template>
	<k-field v-bind="$props" :input="_uid" class="k-date-field">
		<div ref="body" :data-has-time="Boolean(time)" class="k-date-field-body">
			<k-date-inputbox
				:id="_uid"
				ref="dateInput"
				v-bind="$props"
				:value="iso.date"
				@input="onDateInput"
			/>
			<k-time-inputbox
				v-if="time"
				ref="timeInput"
				v-bind="time"
				:disabled="disabled"
				:required="required"
				:times="times"
				:value="iso.time"
				@input="onTimeInput"
			/>
		</div>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/DateInputbox.vue";

/**
 * Form field to handle a date/datetime value.
 *
 * Have a look at `<k-field>`, `<k-date-inputbox>` and `<k-time-inputbox>`
 * for additional information.
 *
 * @example <k-date-field :value="value" label="Date" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false,
	props: {
		/**
		 * Time options (e.g. `display`, `icon`, `step`).
		 * Please check docs for `k-time-input` props.
		 * @example { display: 'HH:mm', step: { unit: "minute", size: 30 } }
		 */
		time: {
			type: [Boolean, Object],
			default: () => ({})
		},
		/**
		 * Deactivate the times dropdown or not
		 */
		times: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			// keep an object of separate ISO values
			// for date and time parts
			iso: this.toIso(this.value)
		};
	},
	computed: {
		/**
		 * Whether the field is empty
		 * @returns {bool}
		 */
		isEmpty() {
			if (this.time) {
				return this.iso.date === null && this.iso.time;
			}

			return this.iso.date === null;
		}
	},
	watch: {
		value(newValue, oldValue) {
			if (newValue !== oldValue) {
				this.iso = this.toIso(newValue);
			}
		}
	},
	methods: {
		/**
		 * Focuses the input element
		 * @public
		 */
		focus() {
			this.$refs.dateInput.focus();
		},
		/**
		 * Returns an object of ISO date and time parts
		 * for the current date/time
		 * @returns {Object}
		 */
		now() {
			const now = this.$library.dayjs();
			return {
				date: now.toISO("date"),
				time: this.time ? now.toISO("time") : "00:00:00"
			};
		},
		/**
		 * Handle any input action
		 */
		onInput() {
			if (this.isEmpty) {
				return this.$emit("input", "");
			}

			const dt = this.$library.dayjs.iso(this.iso.date + " " + this.iso.time);

			if (!dt) {
				if (this.iso.date === null || this.iso.time === null) {
					return;
				}
			}

			this.$emit("input", dt?.toISO() ?? "");
		},
		/**
		 * Handle input event from date input
		 * @param {string} value
		 */
		onDateInput(value) {
			// fill in the current time if the time input is empty
			if (value && !this.iso.time) {
				this.iso.time = this.now().time;
			}

			this.iso.date = value;
			this.onInput();
		},
		/**
		 * Handle input event from time input
		 * @param {string} value
		 */
		onTimeInput(value) {
			// fill in the current date if the date input is empty
			if (value && !this.iso.date) {
				this.iso.date = this.now().date;
			}

			this.iso.time = value;
			this.onInput();
		},
		/**
		 * Convert an ISO string into an object
		 * of date/time part ISO strings
		 * @param {string} value
		 */
		toIso(value) {
			const dt = this.$library.dayjs.iso(value);
			return {
				date: dt?.toISO("date") ?? null,
				time: dt?.toISO("time") ?? null
			};
		}
	}
};
</script>

<style>
.k-date-field-body {
	display: grid;
	gap: var(--spacing-2);
}

@container (min-width: 20rem) {
	/** TODO: .k-date-field-body:has(.k-time-input) */
	.k-date-field-body[data-has-time="true"] {
		grid-template-columns: 1fr minmax(6rem, 9rem);
	}
}
</style>
