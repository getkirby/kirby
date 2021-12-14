<template>
  <input
    ref="input"
    v-bind="{
      autofocus,
      disabled,
      id,
      required
    }"
    v-direction
    :class="`k-text-input k-${type}-input`"
    :placeholder="display"
    :value="formatted"
    autocomplete="false"
    spellcheck="false"
    type="text"
    @blur="onBlur"
    @focus="$emit('focus')"
    @input="onInput($event.target.value)"
    @keydown.down.stop.prevent="onArrowDown"
    @keydown.up.stop.prevent="onArrowUp"
    @keydown.enter="onEnter"
    @keydown.tab="onTab"
  />
</template>

<script>
import { autofocus, disabled, id, required } from "@/mixins/props.js";

import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
  mixins: [autofocus, disabled, id, required],
  props: {
    /**
     * format to parse and display the datetime
     * @example `MM/DD/YY`
     */
    display: {
      type: String,
      default: "DD.MM.YYYY"
    },
    /**
     * The last allowed date
     * @example `2025-12-31 22:30:00`
     */
    max: String,
    /**
     * The first allowed date
     * @example `2020-01-01 01:30:00`
     */
    min: String,
    /**
     * Rounding to the nearest step unit size
     */
    step: {
      type: Object,
      default() {
        return {
          size: 1,
          unit: "day"
        };
      }
    },
    type: {
      type: String,
      default: "date"
    },
    /**
     * The date must be provided as iso date string
     * @example `2012-12-12 22:33:00`
     */
    value: String
  }
};

/**
 * @example <k-input v-model="date" type="date" name="date" />
 */
export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    const dt = this.toDatetime(this.value);

    return {
      dt,
      input: dt
    };
  },
  computed: {
    /**
     * Formatted string for datetime object
     */
    formatted() {
      return this.pattern.format(this.dt);
    },
    /**
     * dayjs pattern to use for ISO format
     */
    iso() {
      return "YYYY-MM-DD HH:mm:ss";
    },
    /**
     * dayjs pattern class for `display` pattern
     */
    pattern() {
      return this.$library.dayjs.pattern(this.display);
    }
  },
  watch: {
    value(value) {
      this.dt = this.input = this.toDatetime(value);
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
  },
  methods: {
    /**
     * Increment/decrement the current dayjs object based on the
     * cursor position in the input element and ensuring steps
     * @param {string} operator `add` or `substract`
     * @public
     */
    alter(operator) {
      // since manipulation command can occur
      // while typing new value, make sure to
      // first commit current input value to `dt` object
      this.dt = this.input;

      // if no parsed result exist, use current datetime
      if (this.dt === null) {
        this.dt = this.$library.dayjs().round(this.step.unit, this.step.size);
      }

      // what unit to alter and by how much:
      // as default use the step unit and size
      let unit = this.step.unit;
      let size = this.step.size;

      // if a part in the input is selected,
      // manipulate that part
      const selected = this.selection();

      if (selected !== null) {
        // handle  meridiem to toggle between am/pm
        // instead of e.g. skipping to next day
        if (selected.unit === "meridiem") {
          operator = this.dt.format("a") === "pm" ? "subtract" : "add";
          unit = "hour";
          size = 12;
        } else {
          // handle manipulation of all other units
          unit = selected.unit;

          // only use step size for step unit,
          // otherwise use size of 1
          if (unit !== this.step.unit) {
            size = 1;
          }
        }
      }

      // change `dt` by determined size and unit
      // and emit as `update` event
      this.dt = this.dt[operator](size, unit).round(
        this.step.unit,
        this.stepsize
      );
      this.$emit("update", this.toISO(this.dt));
      this.$nextTick(() => this.select(selected));
    },
    /**
     * Focuses the input element
     * @public
     */
    focus() {
      this.$refs.input.focus();
    },
    /**
     * Decrement the currently
     * selected input part
     * @public
     */
    onArrowDown() {
      this.alter("subtract");
    },
    /**
     * Increment the currently
     * selected input part
     * @public
     */
    onArrowUp() {
      this.alter("add");
    },
    /**
     * When blurring the input, commit its
     * parsed value as datetime object
     */
    onBlur() {
      this.dt = this.input;
      this.$emit("update", this.toISO(this.dt));
    },
    /**
     * When hitting enter, blur the input
     * but also emit additional event
     */
    async onEnter() {
      await this.$refs.input.blur();
      this.$emit("enter", this.toISO(this.dt));
    },
    onInput(input) {
      let dt = this.pattern.interpret(input);

      if (dt) {
        dt = dt.round(this.step.unit, this.step.size);
      }

      this.input = dt;
      this.onInvalid(input && !dt);
      this.$emit("input", this.toISO(this.input));
    },
    onInvalid($invalid, $v) {
      this.$emit("invalid", $invalid || this.$v.$invalid, $v || this.$v);
    },
    /**
     * Handle tab key in input
     *
     * a. cursor is somewhere in the input, no selection
     *    => select the part where the cursor is located
     * b. cursor selection already covers a part fully
     *    => select the next part
     * c. cursor selection covers more than one part
     *    => select the last affected part
     * d. cursor selection cover last part
     *    => tab should blur the input, focus on next tabbale element
     */
    onTab(event) {
      // make sure to confirm any current input
      this.dt = this.input;

      this.$nextTick(() => {
        const selection = this.selection();
        // when an exact part is selected
        if (
          selection.start === this.$refs.input.selectionStart &&
          selection.end === this.$refs.input.selectionEnd - 1
        ) {
          // if last part is selected, jump out
          if (selection.index === this.pattern.parts.length - 1) {
            return;
          }

          // select next part
          this.select(this.pattern.parts[selection.index + 1]);
        } else {
          // select default part (step unit)
          this.select();
        }

        // prevent event and propagation
        // so that the focus does not move out of input
        event.preventDefault();
        event.stopPropagation();
      });
    },
    /**
     * Sets the cursor selection in the input element
     * that includes the provided part
     * @param {Object} part
     * @public
     */
    select(part) {
      if (!part) {
        part = this.selection();
      }

      this.$refs.input.setSelectionRange(part.start, part.end + 1);
    },
    /**
     * Get pattern part for current cursor selection
     */
    selection() {
      return this.pattern.at(
        this.$refs.input.selectionStart,
        this.$refs.input.selectionEnd
      );
    },
    /**
     * Converts ISO string to dayjs object
     * @param {string} string
     * @public
     */
    toDatetime(string) {
      const dt = this.$library.dayjs(string, this.iso);

      if (!dt || !dt.isValid()) {
        return null;
      }

      return dt;
    },
    /**
     * Converts dayjs object to ISO string
     * @param {string} string
     * @public
     */
    toISO(dt) {
      if (!dt || !dt.isValid()) {
        return null;
      }

      return dt.format(this.iso);
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
