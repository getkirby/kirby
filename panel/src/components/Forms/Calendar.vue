<template>
  <div class="k-calendar-input">
    <nav>
      <k-button icon="angle-left" @click="prev" />
      <span class="k-calendar-selects">
        <k-select-input
          v-model.number="view.month"
          :options="months"
          :disabled="disabled"
          :required="true"
        />
        <k-select-input
          v-model.number="view.year"
          :options="years"
          :disabled="disabled"
          :required="true"
        />
      </span>
      <k-button icon="angle-right" @click="next" />
    </nav>

    <table class="k-calendar-table">
      <thead>
        <tr>
          <th v-for="day in weekdays" :key="'weekday_' + day">
            {{ day }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="week in numberOfWeeks" :key="'week_' + week">
          <td
            v-for="(day, dayIndex) in days(week)"
            :key="'day_' + dayIndex"
            :aria-current="isToday(day) ? 'date' : false"
            :aria-selected="isSelected(day) ? 'date' : false"
            :data-between="isBetween(day)"
            :data-first="isFirst(day)"
            :data-last="isLast(day)"
            class="k-calendar-day"
          >
            <k-button
              v-if="day"
              :disabled="isDisabled(day)"
              @click="select(day)"
            >
              {{ day }}
            </k-button>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="k-calendar-today" colspan="7">
            <k-button @click="select('today')">
              {{ $t("today") }}
            </k-button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
/**
 * The Calendar component is mainly used for our `DateInput` component, but it could be used as stand-alone calendar as well with a little CSS love.
 * @example <k-calendar value="2012-12-12" @input="selectDate" />
 */
export default {
  props: {
    disabled: Boolean,
    multiple: Boolean,
    max: String,
    min: String,
    /**
     * ISO date string/s, i.e. `2012-12-12`
     */
    value: [Array, String],
  },
  data() {
    return this.toData(this.value);
  },
  computed: {
    numberOfDays() {
      return this.viewDt.daysInMonth();
    },
    numberOfWeeks() {
      return Math.ceil((this.numberOfDays + this.firstWeekday - 1) / 7);
    },
    firstWeekday() {
      const weekday = this.viewDt.day();
      return weekday > 0 ? weekday : 7;
    },
    weekdays() {
      return [
        this.$t('days.mon'),
        this.$t('days.tue'),
        this.$t('days.wed'),
        this.$t('days.thu'),
        this.$t('days.fri'),
        this.$t('days.sat'),
        this.$t('days.sun'),
      ];
    },
    monthnames() {
      return [
        this.$t('months.january'),
        this.$t('months.february'),
        this.$t('months.march'),
        this.$t('months.april'),
        this.$t('months.may'),
        this.$t('months.june'),
        this.$t('months.july'),
        this.$t('months.august'),
        this.$t('months.september'),
        this.$t('months.october'),
        this.$t('months.november'),
        this.$t('months.december'),
      ];
    },
    months() {
      var options = [];

      this.monthnames.forEach((item, index) => {
        // get date object for 1st of the month
        const date = this.toDate(1, index);

        options.push({
          value: index,
          text: item,
          disabled: date.isBefore(this.view.min, "month") ||
                    date.isAfter(this.view.max, "month")
        });
      });

      return options;
    },
    years() {
      var options = [];

      const min = this.view.min
                ? this.view.min.get("year")
                : this.view.year - 20;
      const max = this.view.max
                ? this.view.max.get("year")
                : this.view.year + 20;

      for (var x = min; x <= max; x++) {
        options.push({
          value: x,
          text: this.$helper.pad(x)
        });
      }

      return options;
    },
    viewDt() {
      const dt = `${this.view.year}-${this.view.month + 1}-01 00:00:00`;
      return this.$library.dayjs.utc(dt);
    }
  },
  watch: {
    value(value) {
      const data     = this.toData(value);
      this.datetimes = data.datetimes;
      this.view      = data.view;
    }
  },
  methods: {
    days(week) {
      let days    = [];
      const start = (week - 1) * 7 + 1;

      for (let x = start; x < start + 7; x++) {
        let day = x - (this.firstWeekday - 1);
        if (day <= 0 || day > this.numberOfDays) {
          days.push("");
        } else {
          days.push(day);
        }
      }

      return days;
    },
    isBetween(day) {
      if (
        day === "" ||
        this.multiple == false ||
        this.datetimes.length < 2
      ) {
        return false;
      }

      const date = this.toDate(day);
      return this.isFirst(day) ||
            this.isLast(day) ||
            (
              date.isAfter(this.datetimes[0], "day") &&
              date.isBefore(this.datetimes[1], "day")
            );

    },
    isDisabled(day) {
      const date = this.toDate(day);
      return date.isBefore(this.view.min, "day") ||
             date.isAfter(this.view.max, "day");
    },
    isFirst(day) {
      if (
        day === "" ||
        this.multiple == false ||
        this.datetimes.length < 2
      ) {
        return false;
      }

      const date = this.toDate(day);
      return date.isSame(this.datetimes[0], "day");
    },
    isLast(day) {
      if (
        day === "" ||
        this.multiple == false ||
        this.datetimes.length < 2
      ) {
        return false;
      }

      const date = this.toDate(day);
      return date.isSame(this.datetimes[1], "day");
    },
    isSelected(day) {
      if (day === "") {
        return false;
      }

      const date = this.toDate(day);
      return this.datetimes.some(current => date.isSame(current, "day"));
    },
    isToday(day) {
      return this.toDate(day).isSame(this.toToday(), "day");
    },
    next() {
      let next = this.viewDt.clone().add(1, "month");
      this.show(next);
    },
    prev() {
      let prev = this.viewDt.clone().subtract(1, "month");
      this.show(prev);
    },
    mergeTime(dt1, dt2) {
      return dt1.clone().set("second", dt2.get("second"))
                        .set("minute", dt2.get("minute"))
                        .set("hour", dt2.get("hour"));
    },
    select(day) {
      const reference = this.datetimes[0] || this.toToday();

      if (day === "today") {
        const today = this.mergeTime(this.$library.dayjs(), reference);
        this.datetimes = [today];
        this.show(today);

      } else {
        let date = this.toDate(day);
        date = this.mergeTime(date, reference);

        if (
          this.multiple === false ||
          this.datetimes.length === 0 ||
          this.datetimes.length === 2 ||
          date.isBefore(this.datetimes[0])
        ) {
          this.datetimes = [date];
        } else {
          this.datetimes.push(date);
        }
      }

      const iso = this.multiple ? 
                  this.datetimes.map(date => this.toISO(date)) : 
                  this.toISO(this.datetimes[0]);

      /**
       * The input event is fired when a date is selected. 
       * @property {string} iso data as ISO date string
       */
      this.$emit("input", iso);
    },
    show(date) {
      this.view.year  = date.year();
      this.view.month = date.month();
    },
    toData(value) {
      const today     = this.toToday();
      const datetimes = this.toDatetimes(value);

      return {
        datetimes: datetimes,
        view: {
          month: (datetimes[0] || today).month(),
          year: (datetimes[0] || today).year(),
          min: this.min ? this.$library.dayjs.utc(this.min) : null,
          max: this.max ? this.$library.dayjs.utc(this.max) : null,
        }
      }
    },
    toDate(day, month = this.view.month, year = this.view.year) {
      return this.$library.dayjs.utc(`${year}-${month + 1}-${day} 00:00:00`);
    },
    toDatetimes(value) {
      if (!value) {
        return [];
      }

      if (typeof value === "string") {
        return [this.$library.dayjs.utc(value)];
      }

      return value.map(date => this.$library.dayjs.utc(date));
    },
    toISO(dt) {
      return dt.format("YYYY-MM-DD HH:mm:ss");
    },
    toToday() {
      return this.$library.dayjs.utc();
    },
  }
};
</script>

<style>

.k-calendar-input {
  --cell-padding: .25rem .5rem;

  padding: .5rem;
  background: var(--color-gray-900);
  color: var(--color-light);
  border-radius: var(--rounded-xs);
}
.k-calendar-table {
  table-layout: fixed;
  width: 100%;
  min-width: 15rem;
  padding-top: .5rem;
}

.k-calendar-input > nav {
  display: flex;
  direction: ltr;
}
.k-calendar-input > nav .k-button {
  padding: .5rem;
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
  padding: 0 .5rem;
  font-weight: var(--font-normal);
  font-size: var(--text-sm);
}
.k-calendar-selects .k-select-input:focus-within {
  color: var(--color-focus-light) !important;
}
.k-calendar-input th {
  padding: .5rem 0;
  color: var(--color-gray-500);
  font-size: var(--text-xs);
  font-weight: 400;
  text-align: center;
}
.k-calendar-day .k-button {
  width: 2rem;
  height: 2rem;
  margin: 0 auto;
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
.k-calendar-day:hover .k-button:not([data-disabled]) {
  border-color: rgba(255, 255, 255, .25);
}
.k-calendar-day[aria-current="date"] .k-button {
  color: var(--color-yellow-500);
  font-weight: 500;
}
.k-calendar-day[aria-selected="date"] .k-button {
  border-color: var(--color-focus-light);
  color: var(--color-focus-light);
}
.k-calendar-day[data-between] {
  background: #333;
}
.k-calendar-day[data-first] {
  border-top-left-radius: 100%;
  border-bottom-left-radius: 100%;
}
.k-calendar-day[data-last] {
  border-top-right-radius: 100%;
  border-bottom-right-radius: 100%;
}
.k-calendar-today {
  text-align: center;
  padding-top: .5rem;
}
.k-calendar-today .k-button {
  color: var(--color-focus-light);
  font-size: var(--text-xs);
  padding: 1rem;
}
.k-calendar-today .k-button-text {
  opacity: 1;
}
</style>
