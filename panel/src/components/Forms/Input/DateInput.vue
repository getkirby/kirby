<template>
  <k-text-input
    ref="input"
    v-model="input"
    v-bind="$props"
    :class="`k-${type}-input`"
    :placeholder="placeholder"
    :spellcheck="false"
    type="text"
    @blur="onBlur"
    @input="onInput"
    @invalid="onInvalid"
    @focus="$emit('focus')"
    @keydown.down.stop.prevent="onKey('subtract')"
    @keydown.up.stop.prevent="onKey('add')"
    @keydown.enter.stop.prevent="onEnter"
  />
</template>

<script>
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
    max: String,
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
    value: String
  },
  data() {
    return {
      input: this.toDatetime(this.value)
    };
  },
  computed: {
    /**
     * Takes the display format and splits it into chunks
     */
    chunks() {
      const parts = this.display.split(/[^A-Za-z]/);
      return parts.map(part => part.charAt(0));
    },
    /**
     * Parsed dayjs object of current input
     */
    parsed() {
      // fix lowercased month names
      const input = this.input ? this.$helper.string.ucwords(this.input) : null;

      // loop through parsing patterns to find
      // first result where input is a valid date
      for (let i = 0; i < this.patterns.length; i++) {
        const dt = this.$library.dayjs.utc(input, this.patterns[i]);

        if (dt.isValid()) {
          return dt;
        }
      }
    },
    /**
     *  Generate all possible dayjs parsing patterns
     *  for all chunks of the provided display format
     */
    patterns() {
      let patterns  = [];
      let previous  = [];

      // For each chunk…
      for (let i = 0; i < this.chunks.length; i++) {
        const tokens = this.tokens[this.chunks[i]];

        if (tokens) {
          // … generate all necessary patterns …
          let forChunk = [];

          // … by either just adding all the tokens, if the first chunk …
          if (patterns.length === 0) {
            forChunk = tokens.map(token => [token]);

          // … or adding each token to all patterns from the previous chunk
          } else {
            tokens.forEach(token => {
              forChunk = forChunk.concat(previous.map(prev => prev.concat([token])));
            })
          }
          patterns  = patterns.concat(forChunk);
          previous = forChunk;
          forChunk = [];
        }
      }

      // join components with some separator
      // and make sure the more detailed patterns go first
      return patterns.map(format => format.join(this.separator)).reverse();
    },
    /**
     * How the display format should be displayed as input placeholder
     */
    placeholder() {
      return this.display.toLowerCase();
    },
    /**
     * Takes currently parsed date object ands rounds it to nearest step
     */
    result() {
      return this.parsed ? this.toNearest(this.parsed) : null;
    },
    /**
     * Separator for date format
     */
    separator() {
      return this.display.match(/[^A-Za-z]/)[0];
    },
    /**
     * Match display format chunks to dayjs tokens
     */
    tokens() {
      let tokens = {
        D: ["D", "DD"],
        M: ["MMM", "M", "MM"],
        Y: ["YYYY"]
      };

      // only if format starts with year, also add short year token
      if (this.display.startsWith("Y")) {
        tokens.Y.unshift("YY");
      }

      return tokens;
    },
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
      if (this.result) {
        this.$emit(event, this.result.format("YYYY-MM-DD HH:mm:ss"));
      } else {
        this.$emit(event, "");
      }
    },
    focus() {
      this.$refs.input.focus();
    },
    onBlur() {
      this.input = this.result ? this.toFormat(this.result) : null;
      this.emit("blur");
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
    onKey(operation) {
      let dt;

      // if a result exists already, modify it one step
      if (this.result) {
        dt = this.result.clone()[operation](this.step.size, this.step.unit);

      // otherwise fill with current datetime
      } else {
        dt = this.toNearest(this.$library.dayjs());
      }

      this.input = this.toFormat(dt);
      this.$refs.input.select();
      this.onBlur();
    },
    toDatetime(value, format = true) {
      if (!value) {
        return null;
      }

      const dt = this.$library.dayjs.utc(value);

      if (dt.isValid() === false) {
        return null;
      }

      return format ? this.toFormat(dt) : dt;
    },
    toFormat(dt) {
      return dt.format(this.display);
    },
    toNearest(dt) {
      dt = dt.clone();
      const unit    = this.step.unit === "day" ? "date" : this.step.unit;
      const current = dt.get(unit);
      const nearest = Math.round(current / this.step.size) * this.step.size;
      return dt.set(unit, nearest).startOf(unit);
    }
  },
  validations() {
    return {
      value: {
        min: this.min ? value => this.$helper.validate.datetime(
          this,
          value,
          this.min,
          "isAfter"
        ) : true,
        max: this.max ? value => this.$helper.validate.datetime(
          this,
          value,
          this.max,
          "isBefore"
        ) : true,
      }
    }
  }
};
</script>
