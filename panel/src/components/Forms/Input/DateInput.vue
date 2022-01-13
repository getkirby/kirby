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
    autocomplete="off"
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
     * Format to parse and display the datetime
     * @values YYYY, YY, MM, M, DD, D
     * @example `MM/DD/YY`
     */
    display: {
      type: String,
      default: "DD.MM.YYYY"
    },
    /**
     * The last allowed date as ISO datetime string
     * @example `2025-12-31 22:30:00`
     */
    max: String,
    /**
     * The first allowed date as ISO datetime string
     * @example `2020-01-01 01:30:00`
     */
    min: String,
    /**
     * Rounding to the nearest step.
     * Requires an object with a `unit`
     * and a `size` key
     * @example { unit: 'minute', size: 30 }
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
     * Value must be provided as ISO datetime string
     * @example `2012-12-12 22:33:00`
     */
    value: String
  }
};

/**
 * Form input to handle a date value.
 *
 * Component allows free input and tries to parse the
 * input value based on a provided `display` format pattern.
 * Support rounding to a nearest `step` as well as keyboard
 * interactions (altering value by arrow up/down, selecting of
 * input parts via tab key).
 *
 * @example <k-input v-model="date" type="date" name="date" />
 * @public
 */
export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      dt: this.toDatetime(this.value)
    };
  },
  computed: {
    /**
     * Formatted string for datetime object
     * @returns {string}
     */
    formatted() {
      return this.pattern.format(this.dt);
    },
    /**
     * dayjs pattern class for `display` pattern
     * @returns {Object}
     */
    pattern() {
      return this.$library.dayjs.pattern(this.display);
    }
  },
  watch: {
    value(value) {
      this.dt = this.toDatetime(value);
      this.onInvalid();
    }
  },
  created() {
    this.onInput = this.$helper.debounce(this.onInput, 500);
  },
  mounted() {
    this.onInvalid();
    // make sure to commit input value when Cmd+S is hit
    this.$events.$on("keydown.cmd.s", this.onBlur);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.onBlur);
  },
  methods: {
    /**
     * Increment/decrement the current dayjs object based on the
     * cursor position in the input element and ensuring steps
     * @param {string} operator `add` or `substract`
     */
    alter(operator) {
      // since manipulation command can occur while
      // typing new value, make sure to first update
      // datetime object from current input value
      this.dt = this.parse();

      // defaults for step
      const step = this.toStep();

      // if no parsed result exist, use current datetime
      if (this.dt === null) {
        this.dt = this.$library.dayjs().round(step.unit, step.size);
      }

      // what unit to alter and by how much:
      // as default use the step unit and size
      let unit = step.unit;
      let size = step.size;

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
          if (unit !== step.unit) {
            size = 1;
          }
        }
      }

      // change `dt` by determined size and unit
      // and emit as `update` event
      this.dt = this.dt[operator](size, unit).round(step.unit, step.size);
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
     */
    onArrowDown() {
      this.alter("subtract");
    },
    /**
     * Increment the currently
     * selected input part
     */
    onArrowUp() {
      this.alter("add");
    },
    /**
     * When blurring the input, update
     * datetime object from parsed value
     */
    onBlur() {
      this.dt = this.parse();
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
    /**
     * Parse the current input value and
     * emit it as well as check the validation
     */
    onInput() {
      const dt = this.parse();
      this.$emit("input", this.toISO(dt));

      // highlight as invalid if input isn't empty
      // but cannot be parsed as datetime object
      this.onInvalid(this.$refs.input.value && !dt);
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
     *
     * @param {Event} event
     */
    onTab(event) {
      // step out of the field if it is empty
      if (this.$refs.input.value == "") {
        return;
      }

      // make sure to confirm any current input
      this.onBlur();

      this.$nextTick(() => {
        const selection = this.selection();

        // if an exact part is selected
        if (
          selection.start === this.$refs.input.selectionStart &&
          selection.end === this.$refs.input.selectionEnd - 1
        ) {
          // move backward on shift + tab
          if (event.shiftKey) {
            // if the first part is selected, jump out
            if (selection.index === 0) {
              return;
            }

            // select previous part
            this.selectPrev(selection.index);

            // move forward on tab
          } else {
            // if the last part is selected, jump out
            if (selection.index === this.pattern.parts.length - 1) {
              return;
            }

            // select next part
            this.selectNext(selection.index);
          }
        } else {
          // select default part (step unit)
          event.shiftKey ? this.selectLast() : this.selectFirst();
        }

        // prevent event and propagation
        // so that the focus does not move out of input
        event.preventDefault();
        event.stopPropagation();
      });
    },
    /**
     * Takes current input value and
     * tries to interpret it as datetime object
     * based on the `display` pattern
     * @return {Object|null}
     */
    parse() {
      // get value and try to interpret
      const input = this.$refs.input.value;

      let dt = this.pattern.interpret(input);

      // round to nearest step
      const step = this.toStep();
      dt = dt?.round(step.unit, step.size) || null;

      return dt;
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
     * Selects the first pattern if available
     * @public
     */
    selectFirst() {
      this.select(this.pattern.parts[0]);
    },
    /**
     * Selects the last pattern if available
     * @public
     */
    selectLast() {
      this.select(this.pattern.parts[this.pattern.parts.length - 1]);
    },
    /**
     * Selects the next pattern if available
     * @param {Number} index
     * @public
     */
    selectNext(index) {
      this.select(this.pattern.parts[index + 1]);
    },
    /**
     * Selects the previous pattern if available
     * @param {Number} index
     * @public
     */
    selectPrev(index) {
      this.select(this.pattern.parts[index - 1]);
    },
    /**
     * Get pattern part for current cursor selection
     * @returns {Object}
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
     * @return {Object|null}
     */
    toDatetime(string) {
      return this.$library.dayjs.iso(string);
    },
    /**
     * Converts dayjs object to ISO string
     * @param {Object} dt
     * @return {Object|null}
     */
    toISO(dt) {
      return dt?.toISO() || null;
    },
    /**
     * Merges step donfiguration with defaults
     * @param {Object} step (default using `this.step`)
     * @returns {Object}
     */
    toStep(step = this.step) {
      return {
        ...this.$options.props.step.default(),
        ...step
      };
    }
  },
  validations() {
    return {
      value: {
        min: this.min
          ? (value) =>
              this.$library
                .dayjs(value)
                .validate(this.min, "min", this.toStep().unit)
          : true,
        max: this.max
          ? (value) =>
              this.$library
                .dayjs(value)
                .validate(this.max, "max", this.toStep().unit)
          : true,
        required: this.required ? validateRequired : true
      }
    };
  }
};
</script>
