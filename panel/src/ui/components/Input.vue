<template>
  <div
    :data-disabled="disabled"
    :data-invalid="!novalidate && isInvalid"
    :data-theme="theme"
    :data-type="type"
    class="k-input"
  >
    <span
      v-if="$slots.before || before"
      class="k-input-before"
      @click="focus"
    >
      <slot name="before">{{ before }}</slot>
    </span>
    <span
      class="k-input-element"
      @click.stop="focus"
    >
      <slot>
        <component
          :is="'k-' + type + '-input'"
          ref="input"
          :value="value"
          v-bind="inputProps"
          v-on="listeners"
        />
      </slot>
    </span>
    <span
      v-if="$slots.after || after"
      class="k-input-after"
      @click="focus"
    >
      <slot name="after">{{ after }}</slot>
    </span>
    <span
      v-if="$slots.icon || icon"
      class="k-input-icon"
      @click="focus"
    >
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
  border-radius: $rounded-sm;

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
}
</style>
