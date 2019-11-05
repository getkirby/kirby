<template>
  <div class="k-date-input">
    <k-select-input
      ref="years"
      :aria-label="$t('year')"
      :options="years"
      :disabled="disabled"
      :required="required"
      :value="year"
      placeholder="––––"
      @input="setYear"
      @invalid="onInvalid"
    />
    <span class="k-date-input-separator">-</span>
    <k-select-input
      ref="months"
      :aria-label="$t('month')"
      :options="months"
      :disabled="disabled"
      :required="required"
      :value="month"
      placeholder="––"
      @input="setMonth"
      @invalid="onInvalid"
    />
    <span class="k-date-input-separator">-</span>
    <k-select-input
      ref="days"
      :aria-label="$t('day')"
      :autofocus="autofocus"
      :id="id"
      :options="days"
      :disabled="disabled"
      :required="required"
      :value="day"
      placeholder="––"
      @input="setDay"
      @invalid="onInvalid"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [String, Number],
    max: String,
    min: String,
    required: Boolean,
    value: String
  },
  data() {
    return {
      date: this.$library.dayjs(this.value),
      minDate: this.calculate(this.min, "min"),
      maxDate: this.calculate(this.max, "max")
    };
  },
  computed: {
    day() {
      return isNaN(this.date.date()) ? "" : this.date.date();
    },
    days() {
      return this.options(1, this.date.daysInMonth() || 31, "days");
    },
    month() {
      return isNaN(this.date.date()) ? "" : this.date.month() + 1;
    },
    months() {
      return this.options(1, 12, "months");
    },
    year() {
      return isNaN(this.date.year()) ? "" : this.date.year();
    },
    years() {
      const start = this.date.isBefore(this.minDate) ? this.date.year() : this.minDate.year();
      const end   = this.date.isAfter(this.maxDate)  ? this.date.year() : this.maxDate.year();

      return this.options(start, end);
    }
  },
  watch: {
    value(value) {
      this.date = this.$library.dayjs(value);
    }
  },
  methods: {
    calculate(value, what) {
      const calc = {
        min: {run: "subtract", take: "startOf"},
        max: {run: "add", take: "endOf" },
      }[what];

      let date = value ? this.$library.dayjs(value) : null;
      if (!date || date.isValid() === false) {
        date = this.$library.dayjs()[calc.run](10, 'year')[calc.take]("year");
      }
      return date;
    },
    focus() {
      this.$refs.years.focus();
    },
    onInput() {
      if (this.date.isValid() === false) {
        this.$emit("input", "");
        return;
      }

      this.$emit("input", this.date.toISOString());
    },
    onInvalid($invalid, $v) {
      this.$emit("invalid", $invalid, $v);
    },
    options(start, end) {
      let options = [];

      for (var x = start; x <= end; x++) {
        options.push({
          value: x,
          text: this.$helper.pad(x)
        });
      }

      return options;
    },
    set(key, value) {

      if (value === "" || value === null || value === false || value === -1) {
        this.setInvalid();
        this.onInput();
        return;
      }

      if (this.date.isValid() === false) {
        this.setInitialDate(key, value);
        this.onInput();
        return;
      }

      let prev    = this.date;
      let prevDay = this.date.date();

      this.date = this.date.set(key, parseInt(value));

      if (key === "month" && this.date.date() !== prevDay) {
        this.date = prev.set("date", 1).set("month", value).endOf("month");
      }

      this.onInput();
    },
    setInvalid() {
      this.date = this.$library.dayjs("invalid");
    },
    setInitialDate(key, value) {
      const current = this.$library.dayjs();

      this.date = this.$library.dayjs().set(key, parseInt(value));

      // if the inital day moved the month, let's move it back
      if (key === "date" && current.month() !== this.date.month()) {
        this.date = current.endOf("month");
      }

      return this.date;
    },
    setDay(day) {
      this.set("date", day);
    },
    setMonth(month) {
      this.set("month", month - 1);
    },
    setYear(year) {
      this.set("year", year);
    }
  }
};
</script>

<style lang="scss">
.k-date-input {
  display: flex;
  align-items: center;
}
.k-date-input-separator {
  padding: 0 $field-input-padding / 4;
}
</style>
