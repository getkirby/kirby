<template>
  <div
    :data-disabled="disabled"
    :data-invalid="validate && isInvalid"
    :data-theme="theme"
    :data-type="type"
    class="kirby-input"
  >
    <span v-if="$slots.before || before" class="kirby-input-before" @click="focus">
      <slot name="before">{{ before }}</slot>
    </span>
    <span class="kirby-input-element" @click.stop="focus">
      <slot>
        <component
          ref="input"
          :is="'kirby-' + type + '-input'"
          :value="value"
          v-bind="inputProps"
          v-on="listeners"
        />
      </slot>
    </span>
    <span v-if="$slots.after || after" class="kirby-input-after" @click="focus">
      <slot name="after">{{ after }}</slot>
    </span>
    <span v-if="$slots.icon || icon" class="kirby-input-icon" @click="focus">
      <slot name="icon">
        <kirby-icon :type="icon" />
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
    validate: Boolean,
    value: {
      type: [String, Boolean, Number, Object, Array]
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
      },
      inputProps: {
        ...this.$props,
        ...this.$attrs
      }
    };
  },
  methods: {
    focus(e) {
      // prevent focussing on first input element,
      // if click is already targetting another input element
      if (e.target && e.target.tagName === 'INPUT') {
        e.target.focus();
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
.kirby-input {
  display: flex;
  align-items: center;
  line-height: 1;
  border: 0;
  outline: 0;
  background: none;
}
.kirby-input-element {
  flex-grow: 1;
}
.kirby-input-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  line-height: 0;
}

/* Disabled state */
.kirby-input[data-disabled] {
  pointer-events: none;
}

.kirby-input[data-theme="field"] {
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

  &[data-invalid] {
    border: $field-input-invalid-border;
    box-shadow: 0;
  }

  &[data-invalid]:focus-within {
    border: $field-input-invalid-focus-border;
    box-shadow: $color-negative-outline 0 0 0 2px;
  }

  .kirby-input-icon {
    width: $field-input-height;
  }
  .kirby-input-icon,
  .kirby-input-before,
  .kirby-input-after {
    align-self: stretch;
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }
  .kirby-input-before,
  .kirby-input-after {
    padding: 0 $field-input-padding;
  }
  .kirby-input-before {
    color: $field-input-color-before;
    padding-right: 0;
  }
  .kirby-input-after {
    color: $field-input-color-after;
    padding-left: 0;
  }

  .kirby-input-icon > .kirby-dropdown {
    width: 100%;
    height: 100%;
  }
  .kirby-input-icon-button {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .kirby-number-input,
  .kirby-select-input,
  .kirby-text-input {
    padding: $field-input-padding;
    line-height: $field-input-line-height;
  }

  .kirby-date-input .kirby-select-input,
  .kirby-time-input .kirby-select-input {
    padding-left: 0;
    padding-right: 0;
  }
  .kirby-date-input .kirby-select-input:first-child,
  .kirby-time-input .kirby-select-input:first-child {
    padding-left: $field-input-padding;
  }

  .kirby-date-input .kirby-select-input:focus-within,
  .kirby-time-input .kirby-select-input:focus-within {
    color: $color-focus;
    font-weight: $font-weight-bold;
  }
  .kirby-time-input .kirby-time-input-meridiem {
    padding-left: $field-input-padding;
  }

  /* Checkboxes */
  &[data-type="checkboxes"] {
    .kirby-input-before {
      border-right: 1px solid $color-background;
    }
    .kirby-input-element + .kirby-input-after,
    .kirby-input-element + .kirby-input-icon {
      border-left : 1px solid $color-background;
    }
    .kirby-checkboxes-input li {
      border-bottom: 1px solid $color-background;
    }
    .kirby-checkboxes-input li:last-child {
      border-bottom: 0;
    }
    .kirby-checkboxes-input label {
      display: block;
      line-height: $field-input-line-height;
      padding: $field-input-padding $field-input-padding;
    }
    .kirby-checkbox-input-icon {
      top: ($field-input-height - $field-input-font-size) / 2;
      left: $field-input-padding;
      margin-top: -1px;
    }
  }

  /* Radio */
  &[data-type="radio"] {
    .kirby-input-before {
      border-right: 1px solid $color-background;
    }
    .kirby-input-element + .kirby-input-after,
    .kirby-input-element + .kirby-input-icon {
      border-left : 1px solid $color-background;
    }
    .kirby-radio-input li {
      display: flex;
      flex-grow: 1;
      align-items: flex-start;
      flex-shrink: 0;
      border-bottom: 1px solid $color-background;
    }
    .kirby-radio-input li:last-child {
      border-bottom: 0;
    }
    .kirby-radio-input label {
      display: block;
      flex-grow: 1;
      min-height: $field-input-height;
      line-height: $field-input-line-height;
      padding: (($field-input-height - $field-input-line-height) / 2) $field-input-padding;
    }
    .kirby-radio-input label::before {
      top: ($field-input-height - 1rem) / 2;
      left: $field-input-padding;
      margin-top: -1px;
    }
    .kirby-radio-input .kirby-radio-input-info {
      display: block;
      font-size: $font-size-small;
      color: $color-dark-grey;
      line-height: $field-input-line-height;
      padding-top: $field-input-line-height / 10;
    }
    .kirby-radio-input .kirby-icon {
      width: $field-input-height;
      height: $field-input-height;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  /* Range */
  &[data-type="range"] {
    .kirby-range-input {
      padding: $field-input-padding;
    }
  }

  /* Select Boxes */
  &[data-type="select"] {
    position: relative;

    .kirby-input-icon {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
    }
  }

  /* Tags */
  &[data-type="tags"] {
    .kirby-tags-input {
      padding: .25rem .25rem 0 .25rem;
    }
    .kirby-tag {
      margin-right: .25rem;
      margin-bottom: .25rem;
      height: 1.75rem;
      font-size: $font-size-small;
    }
    .kirby-tags-input input {
      font-size: $font-size-small;
      padding: 0 .25rem;
      height: 1.75rem;
      line-height: 1;
      margin-bottom: .25rem;
    }
    .kirby-tags-input .kirby-dropdown-content {
      top: calc(100% + .5rem + 2px);
    }
  }

  /* Textarea */
  &[data-type="textarea"] {
    .kirby-textarea-input-native {
      padding: .25rem $field-input-padding;
      line-height: 1.5rem;
    }
  }

  /* Toggle */
  &[data-type="toggle"] {
    .kirby-input-before {
      padding-right: $field-input-padding / 2;
    }
    .kirby-toggle-input {
      padding-left: $field-input-padding;
    }
    .kirby-toggle-input-label {
      padding: 0 $field-input-padding 0 .75rem;
      line-height: $field-input-height;
    }
  }

}
</style>
