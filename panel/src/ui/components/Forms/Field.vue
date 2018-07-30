<template>
  <div
    :data-disabled="disabled"
    class="kirby-field"
    @focusin="$emit('focus', $event)"
  >
    <slot name="header">
      <header class="kirby-field-header">
        <slot name="label">
          <label :for="input" class="kirby-field-label">{{ labelText }} <abbr v-if="required" title="This field is required">*</abbr></label>
        </slot>
        <slot name="options" />
        <slot name="counter">
          <kirby-counter
            v-if="counter"
            v-bind="counter"
            :required="required"
            class="kirby-field-counter"
          />
        </slot>
      </header>
    </slot>
    <slot />
    <slot name="footer">
      <footer v-if="help || $slots.help" class="kirby-field-footer">
        <slot name="help">
          <kirby-text v-if="help" theme="help" class="kirby-field-help">
            {{ help }}
          </kirby-text>
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
}
</script>

<style lang="scss">
.kirby-field-label {
  font-weight: $font-weight-bold;
  display: block;
  padding: 0 0 .75rem;
  flex-grow: 1;
  line-height: 1.25rem;
}
.kirby-field-label abbr {
  text-decoration: none;
  color: $color-light-grey;
  padding-left: .25rem;
}
.kirby-field-header {
  display: flex;
  align-items: baseline;
}
.kirby-field[data-disabled] {
  cursor: not-allowed;
}
.kirby-field[data-disabled] * {
  pointer-events: none;
}
.kirby-field-counter {
  display: none;
}
.kirby-field:focus-within > .kirby-field-header > .kirby-field-counter {
  display: block;
}
.kirby-field-help {
  padding-top: .5rem;
}
</style>
