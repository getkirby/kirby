<template>
	<fieldset
		:class="['k-calendar-input', $attrs.class]"
		:style="$attrs.style"
		@click.stop
	>
		<legend class="sr-only">{{ $t("date.select") }}</legend>
		<!-- Month + year selects -->
		<nav>
			<k-button :title="$t('prev')" icon="angle-left" @click="onPrev" />
			<span class="k-calendar-selects">
				<k-select-input
					:aria-label="$t('month')"
					:autofocus="autofocus"
					:options="months"
					:empty="false"
					:required="true"
					:value="month"
					@input="month = Number($event)"
				/>
				<k-select-input
					:aria-label="$t('year')"
					:options="years"
					:empty="false"
					:required="true"
					:value="year"
					@input="year = Number($event)"
				/>
			</span>
			<k-button :title="$t('next')" icon="angle-right" @click="onNext" />
		</nav>
		<table :key="year + '-' + month" class="k-calendar-table">
			<!-- Weekdays -->
			<thead>
				<tr>
					<th v-for="day in weekdays" :key="'weekday_' + day">
						{{ $t("days." + day) }}
					</th>
				</tr>
			</thead>
			<!-- Dates grid -->
			<tbody>
				<tr v-for="week in weeks" :key="'week_' + week">
					<td
						v-for="(day, dayIndex) in days(week)"
						:key="'day_' + dayIndex"
						:aria-current="isToday(day) ? 'date' : false"
						:aria-selected="isSelected(day) ? 'date' : false"
						class="k-calendar-day"
					>
						<k-button
							v-if="day"
							:disabled="isDisabled(day)"
							:text="day"
							@click="select(toDate(day))"
						/>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<!-- Today button -->
				<tr>
					<td class="k-calendar-today" colspan="7">
						<k-button
							:disabled="disabled"
							:text="$t('today')"
							@click="
								show(today);
								select(today);
							"
						/>
					</td>
				</tr>
			</tfoot>
		</table>

		<!-- Hidden input for validation -->
		<input
			:id="id"
			:disabled="disabled"
			:formnovalidate="novalidate"
			:min="min"
			:max="max"
			:name="name"
			:required="required"
			:value="value"
			class="input-hidden"
			tabindex="-1"
			type="date"
		/>
	</fieldset>
</template>

<script>
import { props as InputProps } from "@/mixins/input.js";
import { IsoDateProps } from "./DateInput.vue";

const days = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];

/**
 * The Calendar component is mainly used for our `DateInput` component, but it could be used as stand-alone calendar as well with a little CSS love.
 * @since 4.0.0
 *
 * @example <k-calendar-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [InputProps, IsoDateProps],
	data() {
		const today = this.$library.dayjs();

		return {
			maxdate: null,
			mindate: null,
			month: today.month(),
			selected: null,
			today: today,
			year: today.year()
		};
	},
	computed: {
		/**
		 * Number of days in the current month
		 * @returns {number}
		 */
		numberOfDays() {
			return this.toDate().daysInMonth();
		},
		/**
		 * Weekday column index (0-6) the first day of the month
		 * needs to be placed at
		 * @returns {number}
		 */
		firstWeekday() {
			const day = days[this.toDate().day()];
			return this.weekdays.indexOf(day);
		},
		/**
		 * Ordered weekday names abbreviations
		 * @returns {array}
		 */
		weekdays() {
			const first = this.$panel.translation.weekday;
			return [...days.slice(first), ...days.slice(0, first)];
		},
		/**
		 * Weeks in the currently viewed month
		 * @returns {number}
		 */
		weeks() {
			// how many weeks/rows do we need
			// to cover offset and all days
			return Math.ceil((this.numberOfDays + this.firstWeekday) / 7);
		},
		/**
		 * Translated month names
		 * @returns {array}
		 */
		monthnames() {
			return [
				"january",
				"february",
				"march",
				"april",
				"may",
				"june",
				"july",
				"august",
				"september",
				"october",
				"november",
				"december"
			].map((day) => this.$t("months." + day));
		},
		/**
		 * Select options for all months
		 * @returns {array}
		 */
		months() {
			var options = [];

			this.monthnames.forEach((item, index) => {
				options.push({
					value: index,
					text: item
				});
			});

			return options;
		},
		/**
		 * Select options for all years
		 * (either from min to max or +/-20 years from current view)
		 * @returns {array}
		 */
		years() {
			const min = this.year - 20;
			const max = this.year + 20;
			return this.toOptions(min, max);
		}
	},
	watch: {
		max: {
			handler(newValue, oldValue) {
				if (newValue === oldValue) {
					return;
				}

				this.maxdate = this.$library.dayjs.interpret(newValue, "date");
			},
			immediate: true
		},
		min: {
			handler(newValue, oldValue) {
				if (newValue === oldValue) {
					return;
				}

				this.mindate = this.$library.dayjs.interpret(newValue, "date");
			},
			immediate: true
		},
		value: {
			handler(newValue, oldValue) {
				if (newValue === oldValue) {
					return;
				}

				// set the selected date
				this.selected = this.$library.dayjs.interpret(newValue, "date");
				this.show(this.selected);
			},
			immediate: true
		}
	},
	methods: {
		/**
		 * Dates for a specific week in the current view (month + year)
		 * @param {number} week number of week in the current month
		 * @returns {array}
		 */
		days(week) {
			let days = [];

			const start = (week - 1) * 7 + 1;
			const end = start + 7;

			for (let x = start; x < end; x++) {
				const day = x - this.firstWeekday;
				const isPlaceholder = day <= 0 || day > this.numberOfDays;
				days.push(!isPlaceholder ? day : "");
			}

			return days;
		},
		/**
		 * Whether a specified day in the current view (month + year)
		 * should be disabled
		 * @param {number} day day number
		 * @returns {boolean}
		 */
		isDisabled(day) {
			const date = this.toDate(day);
			return (
				this.disabled ||
				date.isBefore(this.mindate, "day") ||
				date.isAfter(this.maxdate, "day")
			);
		},
		/**
		 * Whether a specified day in the current view (month + year)
		 * is the selected datetime object
		 * @param {number} day day number
		 * @returns {boolean}
		 */
		isSelected(day) {
			return this.toDate(day).isSame(this.selected, "day");
		},
		/**
		 * Whether a specified day in the current view (month + year)
		 * is today's date
		 * @param {number} day day number
		 * @returns {boolean}
		 */
		isToday(day) {
			return this.toDate(day).isSame(this.today, "day");
		},
		/**
		 * Shows the following month
		 */
		onNext() {
			const next = this.toDate().add(1, "month");
			this.show(next);
		},
		/**
		 * Shows the previous month
		 */
		onPrev() {
			const prev = this.toDate().subtract(1, "month");
			this.show(prev);
		},
		/**
		 * Selects a day and updates datetime object
		 * based on current view (month + year)
		 */
		select(date) {
			this.$emit("input", date?.toISO("date") ?? null);
		},
		show(date) {
			// set the view
			this.month = (date ?? this.today).month();
			this.year = (date ?? this.today).year();
		},
		/**
		 * Creates a dayjs object for a specified day and
		 * optional month
		 * @param {number} day
		 * @param {number} month
		 */
		toDate(day = 1, month) {
			return this.$library.dayjs(
				`${this.year}-${(month ?? this.month) + 1}-${day}`
			);
		},
		/**
		 * Generates select options between min and max
		 * @param {number} min
		 * @param {number} max
		 * @returns {array}
		 */
		toOptions(min, max) {
			var options = [];

			for (var x = min; x <= max; x++) {
				options.push({
					value: x,
					text: this.$helper.pad(x)
				});
			}

			return options;
		}
	}
};
</script>

<style>
.k-calendar-input {
	--button-height: var(--height-sm);
	--button-width: var(--button-height);
	--button-padding: 0;
	padding: var(--spacing-2);
	width: min-content;
}
.k-calendar-table {
	table-layout: fixed;
	min-width: 15rem;
}
.k-calendar-input .k-button {
	justify-content: center;
}
.k-calendar-input > nav {
	display: flex;
	direction: ltr;
	align-items: center;
	margin-bottom: var(--spacing-2);
}
.k-calendar-selects {
	flex-grow: 1;
	display: flex;
	align-items: center;
	justify-content: center;
}
[dir="ltr"] .k-calendar-selects {
	direction: ltr;
}
[dir="rtl"] .k-calendar-selects {
	direction: rtl;
}
.k-calendar-selects .k-select-input {
	display: flex;
	align-items: center;
	text-align: center;
	height: var(--button-height);
	padding: 0 0.5rem;
	border-radius: var(--input-rounded);
}
.k-calendar-selects .k-select-input:focus-within {
	outline: var(--outline);
}
.k-calendar-input th {
	padding-block: 0.5rem;
	color: var(--color-gray-500);
	font-size: var(--text-xs);
	text-align: center;
}
.k-calendar-day {
	padding: 2px;
}
.k-calendar-day[aria-current="date"] .k-button {
	text-decoration: underline;
}
.k-calendar-day[aria-selected="date"] .k-button,
.k-calendar-day[aria-selected="date"] .k-button:focus {
	--button-color-text: var(--color-text);
	--button-color-back: var(--color-blue-500);
}
.k-calendar-day[aria-selected="date"] .k-button:focus-visible {
	outline-offset: 2px;
}
.k-calendar-today {
	padding-top: var(--spacing-2);
	text-align: center;
}
.k-calendar-today .k-button {
	--button-width: auto;
	--button-padding: var(--spacing-3);
	font-size: var(--text-xs);
	text-decoration: underline;
}
</style>
