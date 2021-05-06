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
          <!-- eslint-disable vue/no-v-html -->
          <k-text
            v-if="help"
            theme="help"
            class="k-field-help"
            v-html="help"
          />
          <!-- eslint-enable vue/no-v-html -->
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

<style>
.k-field-label {
  font-weight: var(--font-bold);
  display: block;
  padding: 0 0 .75rem;
  flex-grow: 1;
  line-height: 1.25rem;
}
.k-field-label abbr {
  text-decoration: none;
  color: var(--color-gray-500);
  padding-left: .25rem;
}
.k-field-header {
  position: relative;
  display: flex;
  align-items: baseline;
}
.k-field-options {
  position: absolute;
  top: calc(-.5rem - 1px);
}
[dir="ltr"] .k-field-options {
  right: 0;
}
[dir="rtl"] .k-field-options {
  left: 0;
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
  padding-top: .5rem;
}
</style>
