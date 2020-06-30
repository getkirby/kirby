<template>
  <div
    :data-disabled="disabled"
    :data-translate="translate"
    :class="'k-field k-field-name-' + name"
    @focusin="$emit('focus', $event)"
    @focusout="$emit('blur', $event)"
  >
    <slot name="header">
      <header class="k-field-header">
        <slot name="label">
          <label :for="input" class="k-field-label">{{ labelText }} <abbr v-if="required" :title="$t('field.required')">*</abbr></label>
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
    translate: Boolean,
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
  position: relative;
  display: flex;
  align-items: baseline;
}
.k-field-options {
  position: absolute;
  top: calc(-.5rem - 1px);

  [dir="ltr"] & {
    right: 0;
  }

  [dir="rtl"] & {
    left: 0;
  }
}
.k-field-options.k-button-group .k-dropdown {
  height: auto;
}
.k-field-options.k-button-group .k-field-options-button.k-button {
  padding: .75rem;
  display: flex;
}
.k-field[data-disabled] {
  cursor: not-allowed;
  opacity: .4;
}
.k-field[data-disabled] * {
  pointer-events: none;
}
.k-field[data-disabled] .k-text[data-theme=help] * {
  pointer-events: initial;
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
