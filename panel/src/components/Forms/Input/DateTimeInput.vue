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
import DateInput from "./DateInput.vue";
import { required } from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    ...DateInput.props,
    time: {
      type: [Boolean, Object],
      default() {
        return {};
      }
    },
    value: String
  },
  data() {
    return {
      input: this.toDatetime(this.value)
    };
  },
  computed: {
    dateOptions() {
      return {
        autofocus: this.autofocus,
        disabled:  this.disabled,
        display:   this.display,
        id:        this.id,
        required:  this.required,
        value:     this.value
      };
    },
    timeOptions() {
      return {
        ...this.time,
        disabled: this.disabled,
        required: this.required,
        value:    this.value ? this.toDatetime(this.value).format("HH:mm:ss") : null
      }
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
    onUpdate(value, input) {
      const base  = this.toDatetime(this.value);
      input = this.toDatetime(value, input, base);
      this.emit("update", input);
    },
    onEnter(value, input) {
      this.onUpdate(input, value);
      this.emit("enter");
    },
    onInput(value, input) {
      this.input = this.toDatetime(value, input, this.input);
      this.emit("input");
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    toDatetime(value, input, base) {
      // if only value is passed,
      // parse value as dayjs date and
      // return object (or null if invalid)

      if (!value) {
        return null;
      }

      let dt = this.$library.dayjs.utc(value);

      if (input === "time") {
        dt = this.$library.dayjs.utc(value, "HH:mm:ss");
      }

      if (dt.isValid() === false) {
        return null;
      }

      // if also input and base are passed,
      // take the input (date or time) values from value
      // and merge these onto the base dayjs object

      if (!input || !base) {
        return dt;
      }

      if (input === "date") {
        return base.clone().utc().set("year", dt.get("year"))
                                 .set("month", dt.get("month"))
                                 .set("date", dt.get("date"));
      }

      if (input === "time") {
        return base.clone().utc().set("hour", dt.get("hour"))
                                 .set("minute", dt.get("minute"))
                                 .set("second", dt.get("second"));
      }
    }
  },
  validations() {
    return {
      value: {
        min: this.min ? value => this.$helper.validate.datetime(
          this,
          value,
          this.min,
          "isAfter",
          this.step.unit
        ) : true,
        max: this.max ? value => this.$helper.validate.datetime(
          this,
          value,
          this.max,
          "isBefore",
          this.step.unit
        ) : true,
        required: this.required ? required : true,
      }
    };
  }
}

</script>

<style>
.k-datetime-input {
  display: flex;
}
.k-datetime-input .k-time-input {
  padding-left: var(--field-input-padding);
}
</style>
