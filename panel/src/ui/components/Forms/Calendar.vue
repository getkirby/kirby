<template>
  <div class="kirby-calendar-input">
    <nav>
      <kirby-button icon="angle-left" @click="prev" />
      <span class="kirby-calendar-selects">
        <kirby-select-input
          :options="months"
          :disabled="disabled"
          v-model.number="month"
        />
        <kirby-select-input
          :options="years"
          :disabled="disabled"
          v-model.number="year"
        />
      </span>
      <kirby-button icon="angle-right" @click="next" />
    </nav>
    <table class="kirby-calendar-table">
      <thead>
        <tr>
          <th v-for="day in weekdays" :key="'weekday_' + day">{{ day }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="week in numberOfWeeks" :key="'week_' + week">
          <td
            v-for="(day, dayIndex) in days(week)"
            :key="'day_' + dayIndex"
            :aria-current="isToday(day) ? 'date' : false"
            :aria-selected="isCurrent(day) ? 'date' : false"
            class="kirby-calendar-day"
          >
            <kirby-button v-if="day" @click="select(day)">{{ day }}</kirby-button>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="kirby-calendar-today" colspan="7">
            <kirby-button @click="go('today')">{{ "today" | t("field.calendar.today") }}</kirby-button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
import dayjs from "dayjs";
import padZero from "../../helpers/padZero.js";

export default {
  props: {
    value: String,
    disabled: Boolean
  },
  data() {
    const current = dayjs(this.value);

    return {
      day: current.date(),
      month: current.month(),
      year: current.year(),
      today: dayjs(),
      current: current,
    };
  },
  watch: {
    value(value) {
      const current = dayjs(value);
      this.day     = current.date();
      this.month   = current.month();
      this.year    = current.year();
      this.current = current;
    }
  },
  computed: {
    date() {
      return dayjs(`${this.year}-${this.month + 1}-${this.day}`);
    },
    numberOfDays() {
      return this.date.daysInMonth();
    },
    numberOfWeeks() {
      return Math.ceil((this.numberOfDays + this.firstWeekday - 1) / 7);
    },
    firstWeekday() {
      const weekday = this.date.clone().startOf('month').day();
      return weekday > 0 ? weekday : 7;
    },
    weekdays() {
      //TODO: i18n
      return [
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
        'Sun'
      ];
    },
    monthnames() {
      //TODO: i18n
      return [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
      ];
    },
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
    years() {
      var options = [];

      for (var x = this.year - 10; x <= this.year + 10; x++) {
        options.push({
          value: x,
          text: padZero(x)
        });
      }

      return options;
    }
  },
  methods: {
    days(week) {
      let days = [];
      let start = (week - 1) * 7 + 1;

      for (var x = start; x < start + 7; x++) {
        var day = x - (this.firstWeekday - 1);
        if (day <= 0 || day > this.numberOfDays) {
          days.push("");
        } else {
          days.push(day);
        }
      }

      return days;
    },
    next() {
      let next = this.date.clone().add(1, 'month');
      this.set(next);
    },
    isToday(day) {
      return (
        this.month === this.today.month() &&
        this.year === this.today.year() &&
        day === this.today.date()
      );
    },
    isCurrent(day) {
      return (
        this.month === this.current.month() &&
        this.year === this.current.year() &&
        day === this.current.date()
      );
    },
    prev() {
      let prev = this.date.clone().subtract(1, 'month');
      this.set(prev);
    },
    go(year, month) {
      if (year === "today") {
        year = this.today.year();
        month = this.today.month();
      }

      this.year = year;
      this.month = month;
    },
    set(date) {
      this.day = date.date();
      this.month = date.month();
      this.year = date.year();
    },
    select(day) {
      if (day) {
        this.day = day;
      }

      const date = dayjs(new Date(
        this.year,
        this.month,
        this.day,
        this.current.hour(),
        this.current.minute()
      ));

      this.$emit("input", date.toISOString());
    }
  }
};
</script>

<style lang="scss">
$cell-padding: 0.25rem 0.5rem;

.kirby-calendar-input {
  padding: 0.5rem;
  background: $color-dark;
  color: $color-light;
  border-radius: $border-radius;
}
.kirby-calendar-table {
  table-layout: fixed;
  width: 100%;
  min-width: 15rem;
  padding-top: .5rem;
}

.kirby-calendar-input > nav {
  display: flex;

  .kirby-button {
    padding: 0.5rem;
  }
}
.kirby-calendar-selects {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kirby-calendar-selects .kirby-select-input {
  padding: 0 0.5rem;
  font-weight: $font-weight-normal;
  font-size: $font-size-small;
}
.kirby-calendar-selects .kirby-select-input:focus-within {
  color: $color-focus-on-dark !important;
}
.kirby-calendar-input th {
  padding: 0.5rem 0;
  color: $color-light-grey;
  font-size: $font-size-tiny;
  font-weight: 400;
  text-align: center;
}
.kirby-calendar-day .kirby-button {
  width: 2rem;
  height: 2rem;
  margin: 0 auto;
  color: $color-white;
  line-height: 1;
  display: flex;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid transparent;
}
.kirby-calendar-day .kirby-button .kirby-button-text {
  opacity: 1;
}
.kirby-calendar-table .kirby-button:hover {
  color: $color-white;
}
.kirby-calendar-day:hover .kirby-button {
  border-color: rgba($color-white, 0.25);
}
.kirby-calendar-day[aria-current="date"] .kirby-button {
  color: $color-focus-on-dark;
  font-weight: 500;
}
.kirby-calendar-day[aria-selected="date"] .kirby-button {
  border-color: $color-positive-on-dark;
  color: $color-positive-on-dark;
}
.kirby-calendar-today {
  text-align: right;
}
.kirby-calendar-today .kirby-button {
  color: $color-focus-on-dark;
  font-size: $font-size-tiny;
  padding: 0.5rem;
}
.kirby-calendar-today .kirby-button-text {
  opacity: 1;
}
</style>
