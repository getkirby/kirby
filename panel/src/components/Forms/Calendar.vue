<template>
  <div class="k-calendar-input">
    <!-- Month + year selects -->
    <nav>
      <k-button icon="angle-left" @click="onPrev" />
      <span class="k-calendar-selects">
        <k-select-input
          v-model.number="current.month"
          :options="months"
          :disabled="disabled"
          :required="true"
        />
        <k-select-input
          v-model.number="current.year"
          :options="years"
          :disabled="disabled"
          :required="true"
        />
      </span>
      <k-button icon="angle-right" @click="onNext" />
    </nav>

    <table class="k-calendar-table">
      <!-- Weekdays -->
      <thead>
        <tr>
          <th v-for="day in weekdays" :key="'weekday_' + day">
            {{ day }}
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
              @click="select(day)"
            />
          </td>
        </tr>
      </tbody>
      <tfoot>
        <!-- Today button -->
        <tr>
          <td class="k-calendar-today" colspan="7">
            <k-button :text="$t('today')" @click="select('today')" />
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
/**
 * The Calendar component is mainly used for our `DateInput` component, but it could be used as stand-alone calendar as well with a little CSS love.
 * @public
 *
 * @example <k-calendar value="2012-12-12" @input="onInput" />
 */
export default {
  props: {
    /**
     * Disables whole calendar
     */
    disabled: Boolean,
    /**
     * The last allowed date
     * @example `2020-12-31`
     */
    max: String,
    /**
     * The first allowed date
     * @example `2020-01-01`
     */
    min: String,
    /**
     * ISO date/datetime string
     * @example `2020-03-05`
     */
    value: String
  },
  data() {
    return this.data(this.value);
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
     * Adjusted weekday number (Sunday is 7 not 0)
     * @returns {number}
     */
    firstWeekday() {
      const weekday = this.toDate().day();
      return weekday > 0 ? weekday : 7;
    },
    /**
     * Translated weekday names
     * @returns {array}
     */
    weekdays() {
      return ["mon", "tue", "wed", "thu", "fri", "sat", "sun"].map((day) =>
        this.$t("days." + day)
      );
    },
    /**
     * Weeks in the currently viewed month
     * @returns {number}
     */
    weeks() {
      // in which column do we need to start
      const offset = this.firstWeekday - 1;
      // how many weeks/rows do we need
      // to cover offset and all days
      return Math.ceil((this.numberOfDays + offset) / 7);
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
        // get date object for 1st of the month
        const date = this.toDate(1, index);

        options.push({
          value: index,
          text: item,
          disabled:
            date.isBefore(this.current.min, "month") ||
            date.isAfter(this.current.max, "month")
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
      const min = this.current.min?.get("year") ?? this.current.year - 20;
      const max = this.current.max?.get("year") ?? this.current.year + 20;
      return this.toOptions(min, max);
    }
  },
  watch: {
    value(value) {
      const data = this.data(value);
      this.dt = data.dt;
      this.current = data.current;
    }
  },
  methods: {
    /**
     * Internal method to set and update the
     * data object on initialization and when
     * the `value` prop changes
     * @param {string} value
     */
    data(value) {
      const dt = this.$library.dayjs.iso(value);
      const now = this.$library.dayjs();

      return {
        // datetime object of selection
        dt: dt,
        // current calendar view
        current: {
          month: (dt ?? now).month(),
          year: (dt ?? now).year(),
          min: this.$library.dayjs.iso(this.min),
          max: this.$library.dayjs.iso(this.max)
        }
      };
    },
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
        const day = x - (this.firstWeekday - 1);
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
        date.isBefore(this.current.min, "day") ||
        date.isAfter(this.current.max, "day")
      );
    },
    /**
     * Whether a specified day in the current view (month + year)
     * is the selected datetime object
     * @param {number} day day number
     * @returns {boolean}
     */
    isSelected(day) {
      return this.toDate(day).isSame(this.dt, "day");
    },
    /**
     * Whether a specified day in the current view (month + year)
     * is today's date
     * @param {number} day day number
     * @returns {boolean}
     */
    isToday(day) {
      const now = this.$library.dayjs();
      return this.toDate(day).isSame(now, "day");
    },
    /**
     * Emits the current datetime as ISO string
     */
    onInput() {
      /**
       * The input event is fired when a date is selected.
       * @property {string} iso data as ISO date string
       */
      this.$emit("input", this.dt?.toISO("date") || null);
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
    select(day) {
      // when selecting today, make sure to merge in current time selects
      const date =
        day === "today"
          ? this.$library.dayjs().merge(this.toDate(), "time")
          : this.toDate(day);
      this.dt = date;
      this.show(date);
      this.onInput();
    },
    /**
     * Updates the calendar to display the
     * month of the provided dayjs object
     * @param {Object} dt
     */
    show(dt) {
      this.current.year = dt.year();
      this.current.month = dt.month();
    },
    /**
     * Creates a dayjs object for a specified day and
     * optional month
     * @param {number} day
     * @param {number} month
     */
    toDate(day = 1, month = this.current.month) {
      return this.$library.dayjs(`${this.current.year}-${month + 1}-${day}`);
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
  --cell-padding: 0.25rem 0.5rem;

  padding: 0.5rem;
  background: var(--color-gray-900);
  color: var(--color-light);
  border-radius: var(--rounded-xs);
}
.k-calendar-table {
  table-layout: fixed;
  width: 100%;
  min-width: 15rem;
  padding-top: 0.5rem;
}

.k-calendar-input > nav {
  display: flex;
  direction: ltr;
}
.k-calendar-input > nav .k-button {
  padding: 0.5rem;
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
  padding: 0 0.5rem;
  font-weight: var(--font-normal);
  font-size: var(--text-sm);
}
.k-calendar-selects .k-select-input:focus-within {
  color: var(--color-focus-light) !important;
}
.k-calendar-input th {
  padding: 0.5rem 0;
  color: var(--color-gray-500);
  font-size: var(--text-xs);
  font-weight: 400;
  text-align: center;
}
.k-calendar-day .k-button {
  width: 2rem;
  height: 2rem;
  margin-inline: auto;
  color: var(--color-white);
  line-height: 1.75rem;
  display: flex;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid transparent;
}
.k-calendar-day .k-button .k-button-text {
  opacity: 1;
}
.k-calendar-table .k-button:hover {
  color: var(--color-white);
}
.k-calendar-day:hover .k-button:not([data-disabled="true"]) {
  border-color: rgba(255, 255, 255, 0.25);
}
.k-calendar-day[aria-current="date"] .k-button {
  text-decoration: underline;
}
.k-calendar-day[aria-selected="date"] .k-button {
  border-color: currentColor;
  font-weight: 600;
  color: var(--color-focus-light);
}
.k-calendar-today {
  text-align: center;
  padding-top: 0.5rem;
}
.k-calendar-today .k-button {
  font-size: var(--text-xs);
  padding: 1rem;
  text-decoration: underline;
}
.k-calendar-today .k-button-text {
  opacity: 1;
  vertical-align: baseline;
}
</style>
