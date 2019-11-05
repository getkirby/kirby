<template>
  <div
    :data-disabled="disabled"
    :data-invalid="!novalidate && isInvalid"
    :data-theme="theme"
    :data-type="type"
    class="k-input"
  >
    <span v-if="$slots.before || before" class="k-input-before" @click="focus">
      <slot name="before">{{ before }}</slot>
    </span>
    <span class="k-input-element" @click.stop="focus">
      <slot>
        <component
          ref="input"
          :is="'k-' + type + '-input'"
          :value="value"
          v-bind="inputProps"
          v-on="listeners"
        />
      </slot>
    </span>
    <span v-if="$slots.after || after" class="k-input-after" @click="focus">
      <slot name="after">{{ after }}</slot>
    </span>
    <span v-if="$slots.icon || icon" class="k-input-icon" @click="focus">
      <slot name="icon">
        <k-icon :type="icon" />
      </slot>
    </span>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    after: String,
    before: String,
    disabled: Boolean,
    type: String,
    icon: [String, Boolean],
    invalid: Boolean,
    theme: String,
    novalidate: {
      type: Boolean,
      default: false,
    },
    value: {
      type: [String, Boolean, Number, Object, Array],
      default: null
    }
  },
  data() {
    return {
      isInvalid: this.invalid,
      listeners: {
        ...this.$listeners,
        invalid: ($invalid, $v) => {
          this.isInvalid = $invalid;
          this.$emit("invalid", $invalid, $v);
        }
      }
    };
  },
  computed: {
    inputProps() {
      return {
        ...this.$props,
        ...this.$attrs
      };
    }
  },
  methods: {
    blur(e) {
      if (e.relatedTarget && this.$el.contains(e.relatedTarget) === false) {
        // use dedicated blur method if provided
        if (this.$refs.input.blur) {
          this.$refs.input.blur();
          return;
        }
      }
    },
    focus(e) {
      // prevent focussing on first input element,
      // if click is already targetting another input element
      if (e && e.target && e.target.tagName === 'INPUT') {
        e.target.focus();
        return;
      }

      // use dedicated focus method if provided
      if (this.$refs.input && this.$refs.input.focus) {
        this.$refs.input.focus();
        return;
      }

      const input = this.$el.querySelector("input, select, textarea");

      if (input) {
        input.focus();
      }
    }
  }
}
</script>

<style lang="scss">


/* Base Design */
.k-input {
  display: flex;
  align-items: center;
  line-height: 1;
  border: 0;
  outline: 0;
  background: none;
}
.k-input-element {
  flex-grow: 1;
}
.k-input-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  line-height: 0;
}

/* Disabled state */
.k-input[data-disabled] {
  pointer-events: none;
}

.k-input[data-theme="field"] {
  line-height: 1;
  border: $field-input-border;
  background: $field-input-background;

  &:focus-within {
    border: $field-input-focus-border;
    box-shadow: $color-focus-outline 0 0 0 2px;
  }

  &[data-disabled] {
    background: $color-background;
  }

  .k-input-icon {
    width: $field-input-height;
  }
  .k-input-icon,
  .k-input-before,
  .k-input-after {
    align-self: stretch;
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }
  .k-input-before,
  .k-input-after {
    padding: 0 $field-input-padding;
  }
  .k-input-before {
    color: $field-input-color-before;
    padding-right: 0;
  }
  .k-input-after {
    color: $field-input-color-after;
    padding-left: 0;
  }

  .k-input-icon > .k-dropdown {
    width: 100%;
    height: 100%;
  }
  .k-input-icon-button {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .k-number-input,
  .k-select-input,
  .k-text-input {
    padding: $field-input-padding;
    line-height: $field-input-line-height;
  }

  .k-date-input .k-select-input,
  .k-time-input .k-select-input {
    padding-left: 0;
    padding-right: 0;
  }
  .k-date-input .k-select-input:first-child,
  .k-time-input .k-select-input:first-child {
    [dir="ltr"] & {
      padding-left: $field-input-padding;
    }
    [dir="rtl"] & {
      padding-right: $field-input-padding;
    }
  }

  .k-date-input .k-select-input:focus-within,
  .k-time-input .k-select-input:focus-within {
    color: $color-focus;
    font-weight: $font-weight-bold;
  }
  .k-time-input .k-time-input-meridiem {
    padding-left: $field-input-padding;
  }

  /* Checkboxes & Radio Buttons */
  &[data-type=checkboxes],
  &[data-type=radio] {
    .k-checkboxes-input li,
    .k-radio-input li {
      min-width: 0;
      overflow-wrap: break-word;
    }
  }

  /* Checkboxes */
  &[data-type="checkboxes"] {
    .k-input-before {
      border-right: 1px solid $color-background;
    }
    .k-input-element + .k-input-after,
    .k-input-element + .k-input-icon {
      border-left : 1px solid $color-background;
    }
    .k-input-element {
      overflow: hidden;
    }
    .k-checkboxes-input {
      display: grid;
      grid-template-columns: 1fr;
      margin-bottom: -1px;
      margin-right: -1px;

      @media screen and (min-width: $breakpoint-medium) {
        grid-template-columns: repeat(var(--columns), 1fr);
      }

    }
    .k-checkboxes-input li {
      border-right: 1px solid $color-background;
      border-bottom: 1px solid $color-background;
    }
    .k-checkboxes-input label {
      display: block;
      line-height: $field-input-line-height;
      padding: $field-input-padding $field-input-padding;
    }
    .k-checkbox-input-icon {
      top: ($field-input-height - $field-input-font-size) / 2;
      left: $field-input-padding;
      margin-top: 0px;
    }
  }

  /* Radio */
  &[data-type="radio"] {
    .k-input-before {
      border-right: 1px solid $color-background;
    }
    .k-input-element + .k-input-after,
    .k-input-element + .k-input-icon {
      border-left : 1px solid $color-background;
    }
    .k-input-element {
      overflow: hidden;
    }
    .k-radio-input {
      display: grid;
      grid-template-columns: 1fr;
      margin-bottom: -1px;
      margin-right: -1px;

      @media screen and (min-width: $breakpoint-medium) {
        grid-template-columns: repeat(var(--columns), 1fr);
      }
    }
    .k-radio-input li {
      border-right: 1px solid $color-background;
      border-bottom: 1px solid $color-background;
    }
    .k-radio-input label {
      display: block;
      flex-grow: 1;
      min-height: $field-input-height;
      line-height: $field-input-line-height;
      padding: (($field-input-height - $field-input-line-height) / 2) $field-input-padding;
    }
    .k-radio-input label::before {
      top: ($field-input-height - 1rem) / 2;
      left: $field-input-padding;
      margin-top: -1px;
    }
    .k-radio-input .k-radio-input-info {
      display: block;
      font-size: $font-size-small;
      color: $color-dark-grey;
      line-height: $field-input-line-height;
      padding-top: $field-input-line-height / 10;
    }
    .k-radio-input .k-icon {
      width: $field-input-height;
      height: $field-input-height;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  /* Range */
  &[data-type="range"] {
    .k-range-input {
      padding: $field-input-padding;
    }
  }

  /* Select Boxes */
  &[data-type="select"] {
    position: relative;

    .k-input-icon {
      position: absolute;
      top: 0;
      bottom: 0;

      [dir="ltr"] & {
        right: 0;
      }

      [dir="rtl"] & {
        left: 0;
      }
    }
  }

  /* Tags */
  &[data-type="tags"] {
    .k-tags-input {
      padding: .25rem .25rem 0 .25rem;
    }
    .k-tag {
      margin-right: .25rem;
      margin-bottom: .25rem;
      height: 1.75rem;
      font-size: $font-size-small;
    }
    .k-tags-input input {
      font-size: $font-size-small;
      padding: 0 .25rem;
      height: 1.75rem;
      line-height: 1;
      margin-bottom: .25rem;
    }
    .k-tags-input .k-dropdown-content {
      top: calc(100% + .5rem + 2px);
    }
  }

  /* Multiselect */
  &[data-type="multiselect"] {
    position: relative;

    .k-multiselect-input {
      padding: .25rem 2rem 0 .25rem;
      min-height: 2.25rem;
    }
    .k-tag {
      margin-right: .25rem;
      margin-bottom: .25rem;
      height: 1.75rem;
      font-size: $font-size-small;
    }
    .k-input-icon {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
    }
  }

  /* Textarea */
  &[data-type="textarea"] {
    .k-textarea-input-native {
      padding: .25rem $field-input-padding;
      line-height: 1.5rem;
    }
  }

  /* Toggle */
  &[data-type="toggle"] {
    .k-input-before {
      padding-right: $field-input-padding / 2;
    }
    .k-toggle-input {
      padding-left: $field-input-padding;
    }
    .k-toggle-input-label {
      padding: 0 $field-input-padding 0 .75rem;
      line-height: $field-input-height;
    }
  }

}
</style>
