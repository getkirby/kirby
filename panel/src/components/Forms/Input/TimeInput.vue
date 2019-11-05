<template>
  <div class="k-time-input">
    <k-select-input
      ref="hour"
      :id="id"
      :aria-label="$t('hour')"
      :autofocus="autofocus"
      :options="hours"
      :required="required"
      :disabled="disabled"
      v-model="hour"
      placeholder="––"
      @input="setHour"
      @invalid="onInvalid"
    />
    <span class="k-time-input-separator">:</span>
    <k-select-input
      ref="minute"
      :aria-label="$t('minutes')"
      :options="minutes"
      :required="required"
      :disabled="disabled"
      v-model="minute"
      placeholder="––"
      @input="setMinute"
      @invalid="onInvalid"
    />
    <k-select-input
      v-if="notation === 12"
      ref="meridiem"
      :aria-label="$t('meridiem')"
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
      type: String,
    },
  },
  data() {
    const date = this.toObject(this.value);

    return {
      time: this.value,
      hour: date.hour,
      minute: date.minute,
      meridiem: date.meridiem
    };
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
      this.time = value;
    },
    time(time) {
      const date = this.toObject(time);

      this.hour     = date.hour;
      this.minute   = date.minute;
      this.meridiem = date.meridiem;
    },
  },
  methods: {
    focus() {
      this.$refs.hour.focus();
    },
    setHour(hour) {
      if (hour && !this.minute) {
        this.minute = 0;
      }

      if (!hour) {
        this.minute = null;
      }

      this.onInput();
    },
    setMinute(minute) {
      if (minute && !this.hour) {
        this.hour = 0;
      }

      if (!minute) {
        this.hour = null;
      }

      this.onInput();
    },
    onInput() {
      if (this.hour === null || this.minute === null) {
        this.$emit("input", "");
        return;
      }

      const h = this.$helper.pad(this.hour || 0);
      const m = this.$helper.pad(this.minute || 0);
      const a = String(this.meridiem || "AM").toUpperCase();

      const time   = this.notation === 24 ? `${h}:${m}:00` : `${h}:${m}:00 ${a}`;
      const format = this.notation === 24 ? `HH:mm:ss` : `hh:mm:ss A`
      const date = this.$library.dayjs("2000-01-01 " + time, "YYYY-MM-DD " + format);

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
          text: this.$helper.pad(x)
        });
      }

      return options;
    },
    reset() {
      this.hour = null;
      this.minute = null;
      this.meridiem = null;
    },
    round(minute) {
      return Math.floor(minute / this.step) * this.step;
    },
    toObject(time) {

      const date = this.$library.dayjs("2001-01-01 " + time + ":00", "YYYY-MM-DD HH:mm:ss");

      if (!time || date.isValid() === false) {
        return {
          hour: null,
          minute: null,
          meridiem: null
        };
      }

      return {
        hour: date.format(this.notation === 24 ? 'H' : 'h'),
        minute: this.round(date.format('m')),
        meridiem: date.format('A')
      };
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
