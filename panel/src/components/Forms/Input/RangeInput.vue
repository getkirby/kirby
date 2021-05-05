<template>
  <label class="k-range-input">
    <input
      ref="input"
      v-bind="{
        autofocus,
        disabled,
        id,
        max,
        min,
        name,
        required,
        step
      }"
      :value="position"
      :style="`--min: ${min}; --max: ${max}; --value: ${position}`"
      type="range"
      class="k-range-input-native"
      v-on="listeners"
    >
    <span v-if="tooltip" class="k-range-input-tooltip">
      <span v-if="tooltip.before" class="k-range-input-tooltip-before">{{ tooltip.before }}</span>
      <span class="k-range-input-tooltip-text">{{ label }}</span>
      <span v-if="tooltip.after" class="k-range-input-tooltip-after">{{ tooltip.after }}</span>
    </span>
  </label>
</template>

<script>
import { required, minValue, maxValue } from "vuelidate/lib/validators";

/**
 * @example <k-input v-model="range" name="range" type="range" />
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [String, Number],
    default: [Number, String],
    /**
     * The highest accepted number
     */
    max: {
      type: Number,
      default: 100
    },
    /**
     * The lowest required number
     */
    min: {
      type: Number,
      default: 0
    },
    name: [String, Number],
    required: Boolean,
    /**
     * The amount to increment when dragging the slider. This can be a decimal.
     */
    step: {
      type: Number,
      default: 1
    },
    /**
     * The slider tooltip can have text before and after the value.
     */
    tooltip: {
      type: [Boolean, Object],
      default() {
        return {
          before: null,
          after: null
        };
      }
    },
    value: [Number, String]
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        input: (event) => this.onInput(event.target.value)
      }
    };
  },
  computed: {
    baseline() {
      // If the minimum is below 0, the baseline should be placed at .
      // Otherwise place the baseline at the minimum
      return this.min < 0 ? 0 : this.min;
    },
    label() {
      return this.required || (this.value || this.value === 0) ? this.format(this.position) : "–";
    },
    position() {
      return (this.value || this.value === 0) ? this.value : this.default || this.baseline;
    }
  },
  watch: {
    position() {
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();

    if (this.$props.autofocus) {
      this.focus();
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    format(value) {
      const locale = document.lang ? document.lang.replace("_", "-") : 'en';
      const parts  = this.step.toString().split(".");
      const digits = parts.length > 1 ? parts[1].length : 0;
      return new Intl.NumberFormat(locale, {
        minimumFractionDigits: digits
      }).format(value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onInput(value) {
      this.$emit("input", value);
    },
  },
  validations() {
    return {
      position: {
        required: this.required ? required : true,
        min: this.min ? minValue(this.min) : true,
        max: this.max ? maxValue(this.max) : true
      }
    };
  }
}
</script>

<style>
.k-range-input {
  --range-thumb-size: 16px;
  --range-thumb-border: 4px solid var(--color-gray-900);
  --range-thumb-border-disabled: 4px solid var(--color-gray-600);
  --range-thumb-background: var(--color-background);
  --range-thumb-focus-border: 4px solid var(--color-focus);
  --range-thumb-focus-background: var(--color-background);
  --range-track-height: 4px;
  --range-track-background: var(--color-border);
  --range-track-color: var(--color-gray-900);
  --range-track-color-disabled: var(--color-gray-600);
  --range-track-focus-color: var(--color-focus);

  display: flex;
  align-items: center;
}

.k-range-input-native {
  --min: 0;
  --max: 100;
  --value: 0;
  --range: calc(var(--max) - var(--min));
  --ratio: calc((var(--value) - var(--min)) / var(--range));
  --position: calc(0.5 * var(--range-thumb-size) + var(--ratio) * (100% - var(--range-thumb-size)));

  appearance: none;
  width: 100%;
  height: var(--range-thumb-size);
  background: transparent;
  font-size: var(--text-sm);
  line-height: 1;
}
.k-range-input-native::-webkit-slider-thumb {
  appearance: none;
}

.k-range-input-native::-webkit-slider-runnable-track {
  border: none;
  border-radius: var(--range-track-height);
  width: 100%;
  height: var(--range-track-height);
  background: var(--range-track-background);
}

.k-range-input-native::-moz-range-track {
  border: none;
  border-radius: var(--range-track-height);
  width: 100%;
  height: var(--range-track-height);
  background: var(--range-track-background);
}

.k-range-input-native::-ms-track {
  border: none;
  border-radius: var(--range-track-height);
  width: 100%;
  height: var(--range-track-height);
  background: var(--range-track-background);
}

.k-range-input-native::-webkit-slider-runnable-track {
  background: linear-gradient(
      var(--range-track-color), 
      var(--range-track-color)
    )
    0 / var(--position) 
    100% 
    no-repeat 
    var(--range-track-background);
}

.k-range-input-native::-moz-range-progress {
  height: var(--range-track-height);
  background: var(--range-track-color);
}
.k-range-input-native::-ms-fill-lower {
  height: var(--range-track-height);
  background: var(--range-track-color);
}
.k-range-input-native::-webkit-slider-thumb {
  margin-top: calc(0.5 * (var(--range-track-height) - var(--range-thumb-size)));
}

.k-range-input-native::-webkit-slider-thumb {
  box-sizing: border-box;
  width: var(--range-thumb-size);
  height: var(--range-thumb-size);
  background: var(--range-thumb-background);
  border: var(--range-thumb-border);
  border-radius: 50%;
  cursor: pointer;
}
.k-range-input-native::-moz-range-thumb {
  box-sizing: border-box;
  width: var(--range-thumb-size);
  height: var(--range-thumb-size);
  background: var(--range-thumb-background);
  border: var(--range-thumb-border);
  border-radius: 50%;
  cursor: pointer;
}
.k-range-input-native::-ms-thumb {
  box-sizing: border-box;
  width: var(--range-thumb-size);
  height: var(--range-thumb-size);
  background: var(--range-thumb-background);
  border: var(--range-thumb-border);
  border-radius: 50%;
  cursor: pointer;
}
.k-range-input-native::-ms-thumb {
  margin-top: 0;
}
.k-range-input-native::-ms-tooltip {
  display: none;
}

.k-range-input-native:focus {
  outline: none;
}
.k-range-input-native:focus::-webkit-slider-runnable-track {
  border: none;
  border-radius: var(--range-track-height);
  width: 100%;
  height: var(--range-track-height);
  background: var(--range-track-background);
  background: linear-gradient(var(--range-track-focus-color), var(--range-track-focus-color)) 0 / var(--position) 100% no-repeat var(--range-track-background);
}
.k-range-input-native:focus::-moz-range-progress {
  height: var(--range-track-height);
  background: var(--range-track-focus-color);
}
.k-range-input-native:focus::-ms-fill-lower {
  height: var(--range-track-height);
  background: var(--range-track-focus-color);
}
.k-range-input-native:focus::-webkit-slider-thumb {
  background: var(--range-thumb-focus-background);
  border: var(--range-thumb-focus-border);
}
.k-range-input-native:focus::-moz-range-thumb {
  background: var(--range-thumb-focus-background);
  border: var(--range-thumb-focus-border);
}
.k-range-input-native:focus::-ms-thumb {
  background: var(--range-thumb-focus-background);
  border: var(--range-thumb-focus-border);
}

.k-range-input-tooltip {
  position: relative;
  max-width: 20%;
  display: flex;
  align-items: center;
  color: var(--color-white);
  font-size: var(--text-xs);
  line-height: 1;
  text-align: center;
  border-radius: var(--rounded-xs);
  background: var(--color-gray-900);
  margin-left: 1rem;
  padding: 0 .25rem;
  white-space: nowrap;
}
.k-range-input-tooltip::after {
  position: absolute;
  top: 50%;
  left: -5px;
  width: 0;
  height: 0;
  transform: translateY(-50%);
  border-top: 5px solid transparent;
  border-right: 5px solid var(--color-gray-900);
  border-bottom: 5px solid transparent;
  content: "";
}
.k-range-input-tooltip > * {
  padding: 4px;
}

[data-disabled] .k-range-input-native::-webkit-slider-runnable-track {
  background: linear-gradient(var(--range-track-color-disabled), var(--range-track-color-disabled)) 0 / var(--position) 100% no-repeat var(--range-track-background);
}
[data-disabled] .k-range-input-native::-moz-range-progress {
  height: var(--range-track-height);
  background: var(--range-track-color-disabled);
}
[data-disabled] .k-range-input-native::-ms-fill-lower {
  height: var(--range-track-height);
  background: var(--range-track-color-disabled);
}
[data-disabled] .k-range-input-native::-webkit-slider-thumb {
  border: var(--range-thumb-border-disabled);
}
[data-disabled] .k-range-input-native::-moz-range-thumb {
  border: var(--range-thumb-border-disabled);
}
[data-disabled] .k-range-input-native::-ms-thumb {
  border: var(--range-thumb-border-disabled);
}

[data-disabled] .k-range-input-tooltip {
  background: var(--color-gray-600);
}
[data-disabled] .k-range-input-tooltip::after {
  border-right: 5px solid var(--color-gray-600);
}
</style>
