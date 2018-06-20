<template>
  <div class="kirby-date-input">
    <kirby-select-input
      ref="days"
      :autofocus="autofocus"
      :id="id"
      :options="days"
      :disabled="disabled"
      :required="required"
      :value="day"
      placeholder="––"
      empty="––"
      @input="setDay"
      @invalid="onInvalid"
    />
    <span class="kirby-date-input-separator">.</span>
    <kirby-select-input
      ref="months"
      :options="months"
      :disabled="disabled"
      :required="required"
      :value="month"
      empty="––"
      placeholder="––"
      @input="setMonth"
      @invalid="onInvalid"
    />
    <span class="kirby-date-input-separator">.</span>
    <kirby-select-input
      ref="years"
      :options="years"
      :disabled="disabled"
      :required="required"
      :value="year"
      placeholder="––––"
      empty="––––"
      @input="setYear"
      @invalid="onInvalid"
    />
  </div>
</template>

<script>
import dayjs from "dayjs";
import padZero from "../../../helpers/padZero.js";

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
      date: dayjs(this.value),
      minDate: this.calculate(this.min, "min"),
      maxDate: this.calculate(this.max, "max")
    };
  },
  computed: {
    day() {
      return isNaN(this.date.date()) ? "" : this.date.date();
    },
    days() {
      return this.options(1, this.date.daysInMonth() || 0, "days");
    },
    month() {
      return isNaN(this.date.month()) ? "" : this.date.month() + 1;
    },
    months() {
      return this.options(1, 12, "months");
    },
    year() {
      return isNaN(this.date.year()) ? "" : this.date.year();
    },
    years() {
      return this.options(this.minDate.year(), this.maxDate.year());
    }
  },
  watch: {
    value() {
      this.date = dayjs(this.value);
    }
  },
  methods: {
    calculate(value, what) {
      const calc = {
        min: {run: "subtract", take: "startOf"},
        max: {run: "add", take: "endOf" },
      }[what];

      let date = value ? dayjs(value) : null;
      if (!date || date.isValid() === false) {
        date = dayjs()[calc.run](10, 'year')[calc.take]("year");
      }
      return date;
    },
    focus() {
      this.$refs.day.focus();
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
          text: padZero(x)
        });
      }

      return options;
    },
    set(key, value) {
      if (!value) {
        this.date = dayjs("invalid");
        this.onInput();
        return;
      }

      if (this.date.isValid() === false) {
        this.date = dayjs();
      }

      this.date = this.date.set(key, parseInt(value));
      this.onInput();
    },
    setInvalid() {
      this.date = dayjs("invalid");
    },
    setDay(day) {
      this.set("date", day);
    },
    setMonth(month) {
      this.set("month", month);
    },
    setYear(year) {
      this.set("year", year);
    }
  }
};
</script>

<style lang="scss">
.kirby-date-input {
  display: flex;
  align-items: center;
}
.kirby-date-input-separator {
  padding: 0 $field-input-padding / 4;
}
</style>
