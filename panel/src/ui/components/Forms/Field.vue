<template>
  <div
    :data-disabled="disabled"
    :class="'k-field k-field-name-' + name"
    @focusin="$emit('focus', $event)"
    @focusout="$emit('blur', $event)"
  >
    <slot name="header">
      <header class="k-field-header">
        <slot name="label">
          <label :for="input" class="k-field-label">{{ labelText }} <abbr v-if="required" title="This field is required">*</abbr></label>
        </slot>
        <slot name="options" />
        <slot name="counter">
          <k-counter
            v-if="counter"
            v-bind="counter"
            :required="required"
            class="k-field-counter"
          />
        </slot>
      </header>
    </slot>
    <slot />
    <slot name="footer">
      <footer v-if="help || $slots.help" class="k-field-footer">
        <slot name="help">
          <k-text
            v-if="help"
            theme="help"
            class="k-field-help"
            v-html="help"
          />
        </slot>
      </footer>
    </slot>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    counter: [Boolean, Object],
    disabled: Boolean,
    endpoints: Object,
    help: String,
    input: [String, Number],
    label: String,
    name: [String, Number],
    required: Boolean,
    type: String
  },
  computed: {
    labelText() {
      return this.label || " ";
    }
  }
};
</script>

<style lang="scss">
.k-field-label {
  font-weight: $font-weight-bold;
  display: block;
  padding: 0 0 0.75rem;
  flex-grow: 1;
  line-height: 1.25rem;
}
.k-field-label abbr {
  text-decoration: none;
  color: $color-light-grey;
  padding-left: 0.25rem;
}
.k-field-header {
  display: flex;
  align-items: baseline;
}
.k-field[data-disabled] {
  cursor: not-allowed;
}
.k-field[data-disabled] * {
  pointer-events: none;
}
.k-field-counter {
  display: none;
}
.k-field:focus-within > .k-field-header > .k-field-counter {
  display: block;
}
.k-field-help {
  padding-top: 0.5rem;
}
</style>
