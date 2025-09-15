<template>
	<input
		:id="id"
		v-direction
		:autofocus="autofocus"
		:class="['k-string-input', `k-${type}-input`, $attrs.class]"
		:disabled="disabled"
		:placeholder="display"
		:required="required"
		:style="$attrs.style"
		:value="formatted"
		autocomplete="off"
		spellcheck="false"
		type="text"
		@blur="onBlur"
		@focus="$emit('focus')"
		@input="onInput($event.target.value)"
		@keydown.down.stop.prevent="onArrowDown"
		@keydown.up.stop.prevent="onArrowUp"
		@keydown.enter.stop.prevent="onEnter"
		@keydown.meta.s.stop.prevent="onEnter"
		@keydown.ctrl.s.stop.prevent="onEnter"
		@keydown.tab="onTab"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const IsoDateProps = {
	props: {
		/**
		 * The last allowed date as ISO date string
		 * @example "2025-12-31"
		 */
		max: String,
		/**
		 * The first allowed date as ISO date string
		 * @example "2020-01-01"
		 */
		min: String,
		/**
		 * Value must be provided as ISO date string
		 * @example "2012-12-12"
		 */
		value: String
	}
};

export const props = {
	mixins: [InputProps, IsoDateProps],
	props: {
		/**
		 * Format to parse and display the date
		 * @values YYYY, YY, MM, M, DD, D
		 * @example "MM/DD/YY"
		 */
		display: {
			type: String,
			default: "DD.MM.YYYY"
		},

		/**
		 * Rounding to the nearest step.
		 * @value { unit: "second"|"minute"|"hour"|"date"|"month"|"year", size: number }
		 * @example { unit: "minute", size: 30 }
		 */
		step: {
			type: Object,
			default() {
				return {
					size: 1,
					unit: "day"
				};
			}
		},
		type: {
			type: String,
			default: "date"
		}
	}
};

/**
 * Form input to handle a date value.
 *
 * Component allows some degree of free input and parses the
 * input value to a dayjs object. Supports rounding to a
 * nearest `step` as well as keyboard interactions
 * (altering value by arrow up/down, selecting of
 * input parts via tab key).
 *
 * @example <k-input :value="date" @input="date = $event" type="date" name="date" />
 */
export default {
	mixins: [Input, props],
	emits: ["input", "focus", "submit"],
	data() {
		return {
			dt: null,
			formatted: null
		};
	},
	computed: {
		/**
		 * Use the date part for handling input values
		 * @returns {string}
		 */
		inputType() {
			return "date";
		},
		/**
		 * dayjs pattern class for `display` pattern
		 * @returns {Object}
		 */
		pattern() {
			return this.$library.dayjs.pattern(this.display);
		},
		/**
		 * Merges step donfiguration with defaults
		 * @returns {Object}
		 */
		rounding() {
			return {
				...this.$options.props.step.default(),
				...this.step
			};
		}
	},
	watch: {
		value: {
			handler(newValue, oldValue) {
				if (newValue !== oldValue) {
					const dt = this.toDatetime(newValue);
					this.commit(dt);
				}
			},
			immediate: true
		}
	},
	methods: {
		/**
		 * Increment/decrement the current dayjs object based on the
		 * cursor position in the input element and ensuring steps
		 * @param {string} operator `add`|`substract`
		 */
		async alter(operator) {
			// since manipulation command can occur while
			// typing new value, make sure to first update
			// datetime object from current input value
			let dt = this.parse() ?? this.round(this.$library.dayjs());

			// what unit to alter and by how much:
			// as default use the step unit and size
			let unit = this.rounding.unit;
			let size = this.rounding.size;

			// if a part in the input is selected,
			// manipulate that part
			const selected = this.selection();

			if (selected !== null) {
				// handle  meridiem to toggle between am/pm
				// instead of e.g. skipping to next day
				if (selected.unit === "meridiem") {
					operator = dt.format("a") === "pm" ? "subtract" : "add";
					unit = "hour";
					size = 12;
				} else {
					// handle manipulation of all other units
					unit = selected.unit;

					// only use step size for step unit,
					// otherwise use size of 1
					if (unit !== this.rounding.unit) {
						size = 1;
					}
				}
			}

			// change `dt` by determined size and unit
			// and emit as `update` event
			dt = dt[operator](size, unit).round(
				this.rounding.unit,
				this.rounding.size
			);

			this.commit(dt);
			this.emit(dt);

			await this.$nextTick();
			this.select(selected);
		},
		/**
		 * Updates the in data stored dayjs object
		 * as well as formatted string representation
		 * @param {Object} dt dayjs object
		 */
		commit(dt) {
			this.dt = dt;
			this.formatted = this.pattern.format(dt);
			this.validate();
		},
		/**
		 * Convert the dayjs object to an ISO string
		 * and emit the input event
		 * @param {Object} dt dayjs object
		 */
		emit(dt) {
			this.$emit("input", this.toISO(dt));
		},
		/**
		 * Decrement the currently
		 * selected input part
		 */
		onArrowDown() {
			this.alter("subtract");
		},
		/**
		 * Increment the currently
		 * selected input part
		 */
		onArrowUp() {
			this.alter("add");
		},
		/**
		 * When blurring the input, update
		 * data from parsed value and emit
		 */
		onBlur() {
			const dt = this.parse();
			this.commit(dt);
			this.emit(dt);
		},
		/**
		 * When hitting enter, blur the input
		 * but also emit additional submit event
		 */
		async onEnter() {
			// ensure inout gets parsed and emitted as new value
			this.onBlur();
			await this.$nextTick();
			// only thereafter emit submit so the content gets saved
			this.$emit("submit");
		},
		/**
		 * Takes the current input value and
		 * tries to interpret/parse it as dayjs object.
		 * For empty inputs and input values that
		 * already are complete (equal to formatted string),
		 * field emits the current value as input to parent.
		 * @param {string} value
		 */
		onInput(value) {
			// get the parsed dayjs object
			const dt = this.parse();

			// get the formatted string for dayjs value
			const formatted = this.pattern.format(dt);

			// if input is empty or if the input value
			// matches the formatted dayjs interpretation
			// directly commit and emit value
			if (!value || formatted == value) {
				this.commit(dt);
				return this.emit(dt);
			}
		},
		/**
		 * Handle tab key in input
		 *
		 * a. cursor is somewhere in the input, no selection
		 *    => select the part where the cursor is located
		 * b. cursor selection already covers a part fully
		 *    => select the next part
		 * c. cursor selection covers more than one part
		 *    => select the last affected part
		 * d. cursor selection cover last part
		 *    => tab should blur the input, focus on next tabable element
		 * e. cursor is at the end of the pattern
		 *    => tab should blur the input, focus on next tabable element
		 *
		 * @param {Event} event
		 */
		async onTab(event) {
			// step out of the field if it is empty
			if (this.$el.value == "") {
				return;
			}

			// make sure to confirm any current input
			this.onBlur();
			await this.$nextTick();
			const selection = this.selection();

			// if an exact part is selected
			if (
				this.$el &&
				selection.start === this.$el.selectionStart &&
				selection.end === this.$el.selectionEnd - 1
			) {
				// move backward on shift + tab
				if (event.shiftKey) {
					// if the first part is selected, jump out
					if (selection.index === 0) {
						return;
					}

					// select previous part
					this.selectPrev(selection.index);

					// move forward on tab
				} else {
					// if the last part is selected, jump out
					if (selection.index === this.pattern.parts.length - 1) {
						return;
					}

					// select next part
					this.selectNext(selection.index);
				}
			} else {
				// nothing or no part fully selected
				if (
					this.$el &&
					this.$el.selectionStart == selection.end + 1 &&
					selection.index == this.pattern.parts.length - 1
				) {
					// cursor at the end of the pattern, jump out
					return;
				}

				// more than one part selected, select last affected part
				else if (this.$el && this.$el.selectionEnd - 1 > selection.end) {
					const last = this.pattern.at(
						this.$el.selectionEnd,
						this.$el.selectionEnd
					);

					this.select(this.pattern.parts[last.index]);
				}

				// select part where the cursor is located
				else {
					this.select(this.pattern.parts[selection.index]);
				}
			}

			event.preventDefault();
		},
		/**
		 * Takes current input value and
		 * tries to interpret it as datetime object
		 * based on the `display` pattern
		 * @return {Object|null}
		 */
		parse() {
			// interpret the input value
			const value = this.$library.dayjs.interpret(
				this.$el.value,
				this.inputType
			);

			// and round to nearest step
			return this.round(value);
		},
		/**
		 * Rounds the provided dayjs object to
		 * the nearest step
		 * @param {Object} dt dayjs object
		 * @returns {Object|null}
		 */
		round(dt) {
			return dt?.round(this.rounding.unit, this.rounding.size);
		},
		/**
		 * Sets the cursor selection in the input element
		 * that includes the provided part
		 * @param {Object} part
		 * @public
		 */
		select(part) {
			part ??= this.selection();
			this.$el?.setSelectionRange(part.start, part.end + 1);
		},
		/**
		 * Selects the first pattern if available
		 * @public
		 */
		selectFirst() {
			this.select(this.pattern.parts[0]);
		},
		/**
		 * Selects the last pattern if available
		 * @public
		 */
		selectLast() {
			this.select(this.pattern.parts[this.pattern.parts.length - 1]);
		},
		/**
		 * Selects the next pattern if available
		 * @param {Number} index
		 * @public
		 */
		selectNext(index) {
			this.select(this.pattern.parts[index + 1]);
		},
		/**
		 * Selects the previous pattern if available
		 * @param {Number} index
		 * @public
		 */
		selectPrev(index) {
			this.select(this.pattern.parts[index - 1]);
		},
		/**
		 * Get pattern part for current cursor selection
		 * @returns {Object}
		 */
		selection() {
			return this.pattern.at(this.$el.selectionStart, this.$el.selectionEnd);
		},
		/**
		 * Converts ISO string to dayjs object
		 * @param {string} string ISO string
		 * @return {Object|null}
		 */
		toDatetime(string) {
			return this.round(this.$library.dayjs.iso(string, this.inputType));
		},
		/**
		 * Converts dayjs object to ISO string
		 * @param {Object} dt dayjs object
		 * @return {Object|null}
		 */
		toISO(dt) {
			return dt?.toISO(this.inputType);
		},
		validate() {
			const errors = [];

			if (this.required && !this.dt) {
				errors.push(this.$t("error.validation.required"));
			}

			if (
				this.min &&
				this.dt?.validate(this.min, "min", this.rounding.unit) === false
			) {
				errors.push(
					this.$t("error.validation.date.after", {
						date: this.min
					})
				);
			}

			if (
				this.max &&
				this.dt?.validate(this.max, "max", this.rounding.unit) === false
			) {
				errors.push(
					this.$t("error.validation.date.before", {
						date: this.max
					})
				);
			}

			this.$el?.setCustomValidity(errors.join(", "));
		}
	}
};
</script>

<style>
.k-date-input:disabled::placeholder {
	opacity: 0;
}
</style>
