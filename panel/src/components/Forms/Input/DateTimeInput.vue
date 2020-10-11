<template>
  <div class="k-datetime-input">
    <k-date-input
      ref="dateInput"
      v-bind="dateOptions"
      @input="onInput($event, 'date')"
      @blur="onBlur($event, 'date')"
      @enter="onEnter($event, 'date')"
      @focus="$emit('focus')"
    />
    <template v-if="time">
      <k-time-input
        ref="timeInput"
        v-bind="timeOptions"
        @input="onInput($event, 'time')"
        @blur="onBlur($event, 'time')"
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
        value:    this.value
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
    emit(event) {
      if (this.input) {
        this.$emit(event, this.input.format("YYYY-MM-DD HH:mm:ss"));
      } else {
        this.$emit(event, "");
      }
    },
    focus() {
      this.$refs.dateInput.focus();
    },
    onBlur(value, component) {
      const base  = this.toDatetime(this.value);
      this.input  = this.toDatetime(value, component, base);
      this.emit("blur");
    },
    onEnter(value, component) {
      this.onBlur(component, value);
      this.emit("enter");
    },
    onInput(value, component) {
      this.input = this.toDatetime(value, component, this.input);
      this.emit("input");
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    toDatetime(value, component, base) {
      // if only value is passed,
      // parse value as dayjs date  and
      // return object (or null if invalid)

      if (!value) {
        return null;
      }

      let dt = this.$library.dayjs.utc(value);

      if (dt.isValid() === false) {
        return null;
      }

      // if also component and base are passed,
      // take the component (date or time) values from value
      // and merge these onto the base dayjs object

      if (!component || !base) {
        return dt;
      }

      if (component === "date") {
        return base.clone().utc().set("year", dt.get("year"))
                                 .set("month", dt.get("month"))
                                 .set("date", dt.get("date"));
      }

      if (component === "time") {
        return base.clone().utc().set("hour", dt.get("hour"))
                                 .set("minute", dt.get("minute"))
                                 .set("second", dt.get("second"));
      }
    }
  },
  validations() {
    return {
      value: {
        min: this.min ? (value) => {
          const date = this.$library.dayjs.utc(value);
          const min  = this.$library.dayjs.utc(this.min);
          return date.isSame(min) || date.isAfter(min);
        } : true,
        max: this.max ? (value) => {
          const date = this.$library.dayjs.utc(value);
          const max  = this.$library.dayjs.utc(this.max);
          return date.isSame(max) || date.isBefore(max);
        } : true,
        required: this.required ? required : true,
      }
    };
  }
}

</script>

<style lang="scss">
.k-datetime-input {
  display: flex;
}
.k-datetime-input .k-time-input {
  padding-left: $field-input-padding;
}
</style>
