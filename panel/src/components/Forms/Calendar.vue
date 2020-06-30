<template>
  <div class="k-calendar-input">
    <nav>
      <k-button icon="angle-left" @click="prev" />
      <span class="k-calendar-selects">
        <k-select-input
          :options="months"
          :disabled="disabled"
          :required="true"
          v-model.number="month"
        />
        <k-select-input
          :options="years"
          :disabled="disabled"
          :required="true"
          v-model.number="year"
        />
      </span>
      <k-button icon="angle-right" @click="next" />
    </nav>
    <table class="k-calendar-table">
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
            class="k-calendar-day"
          >
            <k-button v-if="day" @click="select(day)">{{ day }}</k-button>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td class="k-calendar-today" colspan="7">
            <k-button @click="selectToday">{{ $t("today") }}</k-button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>

export default {
  props: {
    value: String,
    disabled: Boolean
  },
  data() {

    const current = this.value ? this.$library.dayjs(this.value) : this.$library.dayjs();

    return {
      day: current.date(),
      month: current.month(),
      year: current.year(),
      today: this.$library.dayjs(),
      current: current,
    };
  },
  computed: {
    date() {
      return this.$library.dayjs(`${this.year}-${this.month + 1}-${this.day}`);
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
          text: this.$helper.pad(x)
        });
      }

      return options;
    }
  },
  watch: {
    value(value) {
      const current = this.$library.dayjs(value);
      this.day     = current.date();
      this.month   = current.month();
      this.year    = current.year();
      this.current = current;
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
    selectToday() {
      this.set(this.$library.dayjs());
      this.select(this.day);
    },
    select(day) {

      if (day) {
        this.day = day;
      }

      const date = this.$library.dayjs(new Date(
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

.k-calendar-input {
  padding: 0.5rem;
  background: $color-dark;
  color: $color-light;
  border-radius: $border-radius;
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

  .k-button {
    padding: 0.5rem;
  }
}
.k-calendar-selects {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;

  [dir="ltr"] & {
    direction: ltr;
  }

  [dir="rtl"] & {
    direction: rtl;
  }

}
.k-calendar-selects .k-select-input {
  padding: 0 0.5rem;
  font-weight: $font-weight-normal;
  font-size: $font-size-small;
}
.k-calendar-selects .k-select-input:focus-within {
  color: $color-focus-on-dark !important;
}
.k-calendar-input th {
  padding: 0.5rem 0;
  color: $color-light-grey;
  font-size: $font-size-tiny;
  font-weight: 400;
  text-align: center;
}
.k-calendar-day .k-button {
  width: 2rem;
  height: 2rem;
  margin: 0 auto;
  color: $color-white;
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
  color: $color-white;
}
.k-calendar-day:hover .k-button {
  border-color: rgba($color-white, 0.25);
}
.k-calendar-day[aria-current="date"] .k-button {
  color: $color-focus-on-dark;
  font-weight: 500;
}
.k-calendar-day[aria-selected="date"] .k-button {
  border-color: $color-positive-on-dark;
  color: $color-positive-on-dark;
}
.k-calendar-today {
  text-align: center;
  padding-top: .5rem;
}
.k-calendar-today .k-button {
  color: $color-focus-on-dark;
  font-size: $font-size-tiny;
  padding: 1rem;
}
.k-calendar-today .k-button-text {
  opacity: 1;
}
</style>
