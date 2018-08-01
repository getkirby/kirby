<template>
  <div class="k-time-input">
    <k-select-input
      ref="hour"
      :id="id"
      :autofocus="autofocus"
      :options="hours"
      :required="required"
      :disabled="disabled"
      v-model="hour"
      placeholder="––"
      empty="––"
      @input="onInput"
      @invalid="onInvalid"
    />
    <span class="k-time-input-separator">:</span>
    <k-select-input
      ref="minute"
      :options="minutes"
      :required="required"
      :disabled="disabled"
      v-model="minute"
      placeholder="––"
      empty="––"
      @input="onInput"
      @invalid="onInvalid"
    />
    <k-select-input
      v-if="notation === 12"
      ref="meridiem"
      :empty="false"
      :options="[
        { value: 'AM', text: 'AM' },
        { value: 'PM', text: 'PM' },
      ]"
      :required="required"
      :disabled="disabled"
      v-model="meridiem"
      class="k-time-input-meridiem"
      @input="onInput"
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
    notation: {
      type: Number,
      default: 24
    },
    required: Boolean,
    step: {
      type: Number,
      default: 5
    },
    value: {
      type: String
    },
  },
  data() {
    return this.toObject(this.value);
  },
  computed: {
    hours() {
      return this.options(
        this.notation === 24 ? 0 : 1,
        this.notation === 24 ? 23 : 12
      );
    },
    minutes() {
      return this.options(0, 59, this.step);
    }
  },
  watch: {
    value(value) {
      this.select(this.toObject(value));
    }
  },
  methods: {
    focus() {
      this.$refs.hour.focus();
    },
    onInput(value) {
      if (value === "") {
        this.reset();
        this.$emit("input", null);
        return;
      }

      const h = padZero(this.hour || 0);
      const m = padZero(this.minute || 0);
      const a = this.meridiem || "AM";

      const time = this.notation === 24 ? `${h}:${m}` : `${h}:${m} ${a}`;
      const date = dayjs(new Date("2000-01-01 " + time));

      if (date.isValid() === false) {
        this.$emit("input", null);
        return;
      }

      this.$emit("input", date.format("HH:mm"));
    },
    onInvalid($invalid, $v) {
      this.$emit("invalid", $invalid, $v);
    },
    options(start, end, step = 1) {
      let options = [];

      for (var x = start; x <= end; x += step) {
        options.push({
          value: x,
          text: padZero(x)
        });
      }

      return options;
    },
    reset() {
      this.hour = null;
      this.minute = null;
      this.meridiem = null;
    },
    round(time) {
      time.minute = Math.floor(time.minute / this.step) * this.step;
      return time;
    },
    select(time) {
      this.hour     = time.hour;
      this.minute   = time.minute;
      this.meridiem = time.meridiem;
    },
    toObject(value) {
      const date = dayjs(new Date("2000-01-01 " + value));

      if (date.isValid() === false) {
        return {
          hour: null,
          minute: null,
          meridiem: null
        };
      }

      const time = {
        hour: date.format(this.notation === 24 ? 'H' : 'h'),
        minute: date.format('m'),
        meridiem: date.format('A')
      };

      return this.round(time);
    }
  }
}
</script>

<style lang="scss">
.k-time-input {
  display: flex;
  flex-grow: 1;
  align-items: center;
  line-height: 1;
}
.k-time-input-separator {
  padding: 0 $field-input-padding / 4;
}
.k-time-input-meridiem {
  padding-left: $field-input-padding;
}
</style>
