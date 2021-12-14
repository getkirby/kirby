<template>
  <div class="k-datetime-input">
    <k-date-input
      ref="dateInput"
      v-bind="dateOptions"
      @input="onInput($event, 'date')"
      @update="onUpdate($event, 'date')"
      @enter="onEnter($event, 'date')"
      @focus="$emit('focus')"
    />
    <template v-if="time">
      <k-time-input
        ref="timeInput"
        v-bind="timeOptions"
        @input="onInput($event, 'time')"
        @update="onUpdate($event, 'time')"
        @enter="onEnter($event, 'time')"
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
    time: {
      type: [Boolean, Object],
      default() {
        return {};
      }
    },
    value: String
  }
};

export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      input: this.toDatetime(this.value)
    };
  },
  computed: {
    dateOptions() {
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
        value: this.value
          ? this.toDatetime(this.value).format("HH:mm:ss")
          : null
      };
    }
  },
  watch: {
    value() {
      this.input = this.toDatetime(this.value);
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
  },
  methods: {
    emit(event, dt = this.input) {
      if (dt) {
        this.$emit(event, dt.format("YYYY-MM-DD HH:mm:ss"));
      } else {
        this.$emit(event, "");
      }
    },
    focus() {
      this.$refs.dateInput.focus();
    },

    onEnter(value, units) {
      this.onUpdate(units, value);
      this.emit("enter");
    },
    onInput(value, units) {
      this.input = this.toDatetime(value, units, this.input);
      this.emit("input");
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onUpdate(value, units) {
      const base = this.toDatetime(this.value);
      value = this.toDatetime(value, units, base);
      this.emit("update", value);
    },
    toDatetime(value, units, dt) {
      // if only value is passed,
      // parse value as dayjs date and
      // return object (or null if invalid)

      if (!value) {
        return null;
      }

      let result = this.$library.dayjs.utc(value);

      if (units === "time") {
        result = this.$library.dayjs.utc(value, "HH:mm:ss");
      }

      if (result.isValid() === false) {
        return null;
      }

      // if also input and base are passed,
      // take the input (date or time) values from value
      // and merge these onto the base dayjs object

      if (!units || !dt) {
        return result;
      }

      return dt.update(units, result);
    }
  },
  validations() {
    return {
      value: {
        min: this.min
          ? (value) =>
              this.$library.dayjs
                .utc(value)
                .validate(this.min, "isAfter", this.step.unit)
          : true,
        max: this.max
          ? (value) =>
              this.$library.dayjs
                .utc(value)
                .validate(this.max, "isBefore", this.step.unit)
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
