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
        step,
        value
      }"
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

export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [String, Number],
    max: {
      type: Number,
      default: 100
    },
    min: {
      type: Number,
      default: 0
    },
    name: [String, Number],
    required: Boolean,
    step: {
      type: Number,
      default: 1
    },
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
    label() {
      return this.value !== null ? this.format(this.value) : "–";
    },
    center() {
      const middle = (this.max - this.min) / 2 + this.min;
      return Math.ceil(middle / this.step) * this.step;
    },
    position() {
      return this.value !== null ? this.value : this.center;
    }
  },
  watch: {
    value() {
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
      value: {
        required: this.required ? required : true,
        min: this.min ? minValue(this.min) : true,
        max: this.max ? maxValue(this.max) : true
      }
    };
  }
}
</script>

<style lang="scss">

$range-thumb-size: 16px;
$range-thumb-border: 4px solid $color-dark;
$range-thumb-background: $color-background;
$range-thumb-focus-border: 4px solid $color-focus;
$range-thumb-focus-background: $color-background;
$range-track-height: 4px;
$range-track-background: $color-border;
$range-track-color: $color-dark;
$range-track-focus-color: $color-focus;

@mixin track($fill: 0) {
  border: none;
  border-radius: $range-track-height;
  width: 100%;
  height: $range-track-height;
  background: $range-track-background;
}

@mixin track-background($bg) {
  background: linear-gradient($bg, $bg) 0 / var(--position) 100% no-repeat $range-track-background;
}

@mixin fill($bg) {
  height: $range-track-height;
  background: $bg;
}

@mixin thumb() {
  box-sizing: border-box;
  width: $range-thumb-size;
  height: $range-thumb-size;
  background: $range-thumb-background;
  border: $range-thumb-border;
  border-radius: 50%;
  cursor: pointer;
}
@mixin thumb-focus() {
  background: $range-thumb-focus-background;
  border: $range-thumb-focus-border;
}

.k-range-input {
  display: flex;
  align-items: center;
}

.k-range-input-native {

  --min: 0;
  --max: 100;
  --value: 0;
  --range: calc(var(--max) - var(--min));
  --ratio: calc((var(--value) - var(--min)) / var(--range));
  --position: calc(0.5 * #{$range-thumb-size} + var(--ratio) * (100% - #{$range-thumb-size}));

  appearance: none;
  width: 100%;
  height: $range-thumb-size;
  background: transparent;
  font-size: $font-size-small;
  line-height: 1;

  &::-webkit-slider-thumb {
    appearance: none;
  }
  &::-webkit-slider-runnable-track {
    @include track;
    @include track-background($range-track-color);
  }
  &::-moz-range-track {
    @include track;
  }
  &::-ms-track {
    @include track;
  }
  &::-moz-range-progress {
    @include fill($range-track-color);
  }
  &::-ms-fill-lower {
    @include fill($range-track-color);
  }
  &::-webkit-slider-thumb {
    margin-top: 0.5*($range-track-height - $range-thumb-size);
    @include thumb;
  }
  &::-moz-range-thumb {
    @include thumb;
  }
  &::-ms-thumb {
    margin-top: 0;
    @include thumb;
  }
  &::-ms-tooltip {
    display: none;
  }
}

.k-range-input-native:focus {
  outline: none;

  &::-webkit-slider-runnable-track {
    @include track;
    @include track-background($range-track-focus-color);
  }
  &::-moz-range-progress {
    @include fill($range-track-focus-color);
  }
  &::-ms-fill-lower {
    @include fill($range-track-focus-color);
  }
  &::-webkit-slider-thumb {
    @include thumb-focus;
  }
  &::-moz-range-thumb {
    @include thumb-focus;
  }
  &::-ms-thumb {
    @include thumb-focus;
  }
}

.k-range-input-tooltip {
  position: relative;
  max-width: 20%;
  display: flex;
  align-items: center;
  color: $color-white;
  font-size: $font-size-tiny;
  line-height: 1;
  text-align: center;
  border-radius: $border-radius;
  background: $color-dark;
  margin-left: 1rem;
  padding: 0 .25rem;
  white-space: nowrap;

  &::after {
    position: absolute;
    top: 50%;
    left: -5px;
    width: 0;
    height: 0;
    transform: translateY(-50%);
    border-top: 5px solid transparent;
    border-right: 5px solid $color-dark;
    border-bottom: 5px solid transparent;
    content: "";
  }
}
.k-range-input-tooltip > * {
  padding: 4px;
}
</style>
