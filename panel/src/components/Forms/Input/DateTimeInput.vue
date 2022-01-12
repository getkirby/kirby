<template>
  <div class="k-datetime-input">
    <k-date-input
      ref="dateOptions"
      v-bind="$props"
      @input="onChange"
      @enter="onChange($event, 'enter')"
      @update="onChange($event, 'update')"
      @focus="$emit('focus')"
    />
    <template v-if="time">
      <k-time-input
        ref="timeInput"
        v-bind="timeOptions"
        @input="onChange($event, 'input', 'time')"
        @enter="onChange($event, 'enter', 'time')"
        @update="onChange($event, 'update', 'time')"
        @focus="$emit('focus')"
      />
    </template>
  </div>
</template>

<script>
import { props as DateInput } from "./DateInput.vue";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
  mixins: [DateInput],
  props: {
    /**
     * Time options (e.g. `display`, `step`).
     * Please check docs for `k-time-input` props.
     * @example { display: 'HH:mm', step: { unit: "minute", size: 30 } }
     */
    time: {
      type: [Boolean, Object],
      default() {
        return {};
      }
    }
  }
};

/**
 * Form input to handle a datetime value.
 *
 * Splits and merges value and responses among
 * a separate date input and time input.
 *
 * @example <k-input v-model="value" name="datetime" type="datetime" />
 * @public
 */
export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      dt: this.$library.dayjs.iso(this.value)
    };
  },
  computed: {
    dateOptions() {
      // we don't bind the full $props to the
      // date input so that we can exclude e.g.
      // `min` and `max` since validation should
      // only happen in this component
      return {
        autofocus: this.autofocus,
        disabled: this.disabled,
        display: this.display,
        id: this.id,
        required: this.required,
        value: this.value
      };
    },
    timeOptions() {
      return {
        ...this.time,
        disabled: this.disabled,
        required: this.required,
        value: this.dt?.toISO("time")
      };
    }
  },
  watch: {
    value(value) {
      this.input = this.$library.dayjs.iso(value);
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
  },
  methods: {
    /**
     * Focuses the input element
     * @public
     */
    focus() {
      this.$refs.dateInput.focus();
    },
    /**
     * Process the temporary input and emit it as specified event
     * @param {string} input emitted datetime part as ISO string
     * @param {string} part `date` or `time`
     */
    onChange(input, event = "input", part = "date") {
      // parse input as ISO string (date or time)
      const dt = this.$library.dayjs.iso(input, part);

      // merge specified part (date/time) into `this.dt`
      this.dt = this.dt ? this.dt.merge(dt, part) : dt;

      this.$emit(event, this.dt?.toISO());
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    }
  },
  validations() {
    return {
      value: {
        min: this.min
          ? (value) =>
              this.$library
                .dayjs(value)
                .validate(this.min, "min", this.step.unit)
          : true,
        max: this.max
          ? (value) =>
              this.$library
                .dayjs(value)
                .validate(this.max, "max", this.step.unit)
          : true,
        required: this.required ? validateRequired : true
      }
    };
  }
};
</script>

<style>
.k-datetime-input {
  display: flex;
}
.k-datetime-input .k-time-input {
  padding-inline-start: var(--field-input-padding);
}
</style>
