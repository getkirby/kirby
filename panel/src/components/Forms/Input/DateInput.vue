<template>
  <k-text-input
    ref="input"
    v-model="input"
    v-bind="$props"
    :class="`k-${type}-input`"
    :placeholder="display"
    :spellcheck="false"
    type="text"
    @blur="onBlur"
    @input="onInput"
    @invalid="onInvalid"
    @focus="$emit('focus')"
    @keydown.down.stop.prevent="onDown"
    @keydown.up.stop.prevent="onUp"
    @keydown.enter="onEnter"
    @keydown.tab="onTab"
  />
</template>

<script>
import { required } from "vuelidate/lib/validators";

/**
 * @example <k-input v-model="date" type="date" name="date" />
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    display: {
      type: String,
      default: "DD.MM.YYYY"
    },
    id: [String, Number],
    /**
     * The last allowed date
     */
    max: String,
    /**
     * The first allowed date
     */
    min: String,
    required: Boolean,
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
  },
  data() {
    return {
      input: this.toFormat(this.value),
      selected: null,
    };
  },
  computed: {
    /**
     * Map for matching date units with dayjs tokens
     */
    map() {
      return {
        day:    ["D", "DD"],
        month:  ["M", "MM", "MMM", "MMMM"],
        year:   ["YY", "YYYY"]
      };
    },
    /**
     * Input parsed as dateime object rounded to nearest step
     */
    parsed() {
      if (this.input) {
        // loop through parsing patterns to find
        // first result where input is a valid date
        for (let i = 0; i < this.patterns.length; i++) {
          const dt = this.$library.dayjs.utc(this.input, this.patterns[i]);

          if (dt.isValid()) {
            return dt;
          }
        }
      }

      return null;
    },
    /**
     * Array of the current input parts
     */
    parts() {
      return String(this.input).split(/\W/);
    },
    /**
     *  All variations of parsing patterns
     *  for dayjs tokens included in `display`
     */
    patterns() {
      let patterns = [];
      let previous = [];

      // For each token present…
      for (let i = 0; i < this.tokens.length; i++) {
        // … get variants of the token …
        const tokens = this.toTokens(this.tokens[i]);

        if (tokens) {
          // … and generate all necessary patterns …
          let current = [];

          // … by either just adding all variants, if the first chunk …
          if (patterns.length === 0) {
            current = tokens.map(token => [token]);

          // … or adding each variant to all patterns from the previous token
          } else {
            tokens.forEach(token => {
              current = current.concat(previous.map(prev => prev.concat([token])));
            })
          }
          patterns  = patterns.concat(current);
          previous  = current;
          current   = [];
        }
      }

      // join components with some separator
      // and make sure the more detailed patterns go first
      return patterns.map(format => format.join(this.separator)).reverse();
    },
    /**
     * Separator from `display` format
     */
    separator() {
      return this.display.match(/[\W]/)[0];
    },
    /**
     * Array of used dayjs format tokens
     */
    tokens() {
      return this.display.split(/\W/);
    }
  },
  watch: {
    value(value) {
      this.input = this.toFormat(value);
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
  },
  methods: {
    emit(event) {
      const value = this.toFormat(this.parsed, "YYYY-MM-DD HH:mm:ss") || "";
      this.$emit(event, value);
    },
    focus() {
      this.$refs.input.focus();
    },
    manipulate(operator) {
      let dt;

      // if a parsed result exists already, modify…
      if (this.parsed) {
        // as default use the step unit and size
        let unit = this.step.unit;
        let size = this.step.size;

        // update selected based on cursor position
        this.selected = this.toCursorIndex();

        // if a part in the input is selected,
        // resolve what unit that parts represent
        // and set size to 1 (if not the step unit is selected)
        if (this.selected !== null) {
          const token = this.tokens[this.selected];

          // handle manipulation of am/pm
          if (token.toLowerCase() === "a") {
            unit = "hour";
            size = 12;

            if (this.parts[this.selected] === "pm") {
              operator = "subtract";
            } else {
              operator = "add";
            }

          // handle manipulation of all other units
          } else {
            unit = this.toUnit(token);

            if (unit !== this.step.unit) {
              size = 1;
            }
          }          
        }

        // manipulate datetime by size and unit
        dt = this.parsed.clone()[operator](size, unit);

      // if no parsed result exist, fill with current datetime
      // and mark the part that represent the step unit to be selected
      } else {
        dt = this.toNearest(this.$library.dayjs());
        this.selected = this.toIndex();
      }

      // update input and emit
      this.input = this.toFormat(dt);
      this.emit("update");

      // select modified part in input
      this.$nextTick(() => {
        this.select();
      });
    },
    onBlur() {
      if (!this.parsed) {
        this.input = null;
      }

      this.selected = null;
      this.emit("update");
    },
    onDown() {
      this.manipulate("subtract");
    },
    onEnter() {
      this.onBlur();
      this.emit("enter");
    },
    onInput() {
      this.emit("input");
    },
    onInvalid($invalid, $v) {
      this.$emit("invalid", $invalid || this.$v.$invalid, $v || this.$v);
    },
    onTab(event) {
      const cursor = this.toCursorIndex();

      // nothing has been selected so far,
      // select at cursor position or from start
      if (this.selected === null) {
        this.selected = cursor || 0;

      // cursor position is not at currently selected,
      // select at cursor position
      } else if (cursor !== this.selected) {
        this.selected = cursor;

      // otherwise select next part
      } else {
        this.selected++;
      }

      // if selected is beyong available parts, reset
      if (this.selected >= this.parts.length) {
        this.selected = null;

      // otherwise, capture event and select
      } else {
        event.preventDefault();
        event.stopPropagation();
        this.select();
      }
    },
    onUp() {
      this.manipulate("add");
    },
    select() {
      if (this.selected !== null) {
        // get selection range
        const range = this.toRange(this.selected);

        // make sure to not select leading separator
        if (this.selected > 0) {
          range.start++;
        }

        // select part in input
        this.$refs.input.$refs.input.setSelectionRange(range.start, range.end);
      }
    },
    toCursorIndex() {
      // if whole input is selected, return
      if (
        this.$refs.input.$refs.input.selectionStart === 0 &&
        this.$refs.input.$refs.input.selectionEnd === String(this.input).length
      ) {
        return null;
      }

      // based on the current cursor position,
      // return the matching part's index
      for (let i = 0; i < this.parts.length; i++) {
        const range = this.toRange(i);
        if (
          range.start <= this.$refs.input.$refs.input.selectionStart &&
          range.end >= this.$refs.input.$refs.input.selectionEnd
        ) {
          return i;
        }
      }
    },
    toDatetime(string) {
      return this.$library.dayjs.utc(string);
    },
    toFormat(value, format = this.display) {
      if (!value) {
        return null;
      }

      // parse value as datetime object if string,
      // otherwise expect dayjs object was provided as value
      if (typeof value == "string") {
        value = this.toDatetime(value)
      }

      if (value.isValid() === false) {
        return null;
      }

      // formats datetime according to `display` prop
      return this.toNearest(value).format(format);
    },
    toNearest(dt, unit = this.step.unit, size = this.step.size) {
      // make sure it's dayjs syntax compatible
      if (unit === "day") {
        unit = "date";
      }

      // round datetime to nearest step
      // based on step unit and size
      const current = dt.get(unit);
      const nearest = Math.round(current / size) * size;
      return dt.set(unit, nearest).startOf(unit);
    },
    toIndex(unit = this.step.unit) {
      // get index/position of provided unit
      // in input/display format
      const tokens = this.map[unit];

      for (let i = 0; i < tokens.length; i++) {
        const index = this.tokens.indexOf(tokens[i]);

        if (index !== -1) {
          return index;
        }
      }

    },
    toRange(partIndex) {
      // get an index/position range for the part at provided index
      return {
        start: this.parts.slice(0, partIndex).join(this.separator).length,
        end:   this.parts.slice(0, partIndex + 1).join(this.separator).length
      };
    },
    toTokens(token) {
      // get all token variants for provided token
      const values  = Object.values(this.map);
      const matches = values.filter(tokens => tokens.includes(token));
      return matches[0];
    },
    toUnit(token, nearest = true) {
      // get unit for provided token
      const keys   = Object.keys(this.map);
      const values = Object.values(this.map);
      let index    = values.findIndex(tokens => tokens.includes(token));

      // if nearest unit is required,
      // make sure no unit below the step unit is returned
      const step = this.step.unit;
      if (nearest === true && index < keys.indexOf(step)) {
        return step;
      }

      return keys[index];
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
    }
  }
};
</script>
