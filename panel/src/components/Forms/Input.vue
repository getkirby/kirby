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
          :is="'k-' + type + '-input'"
          ref="input"
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
      if (e && e.relatedTarget && this.$el.contains(e.relatedTarget) === false) {
        this.trigger(null, "blur");
      }
    },
    focus(e) {
      this.trigger(e, "focus");
    },
    select(e) {
      this.trigger(e, "select");
    },
    trigger(e, method) {
      // prevent focussing on first input element,
      // if click is already targetting another input element
      if (e && e.target && e.target.tagName === 'INPUT' && typeof e.target[method] === "function") {
        e.target[method]();
        return;
      }

      // use dedicated focus method if provided
      if (this.$refs.input && typeof this.$refs.input[method] === "function") {
        this.$refs.input[method]();
        return;
      }

      const input = this.$el.querySelector("input, select, textarea");

      if (input && typeof input[method] === "function") {
        input[method]();
      }
    },
  }
}
</script>

<style>
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

[data-disabled] .k-input-icon {
  color: var(--color-gray-600);
}

.k-input[data-theme="field"] {
  line-height: 1;
  border: var(--field-input-border);
  background: var(--field-input-background);
}
.k-input[data-theme="field"]:focus-within {
  border: var(--field-input-focus-border);
  box-shadow: var(--color-focus-outline) 0 0 0 2px;
}

.k-input[data-theme="field"][data-disabled] {
  background: var(--color-background);
}

.k-input[data-theme="field"] .k-input-icon {
  width: var(--field-input-height);
}
.k-input[data-theme="field"] .k-input-icon,
.k-input[data-theme="field"] .k-input-before,
.k-input[data-theme="field"] .k-input-after {
  align-self: stretch;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.k-input[data-theme="field"] .k-input-before,
.k-input[data-theme="field"] .k-input-after {
  padding: 0 var(--field-input-padding);
}
.k-input[data-theme="field"] .k-input-before {
  color: var(--field-input-color-before);
  padding-right: 0;
}
.k-input[data-theme="field"] .k-input-after {
  color: var(--field-input-color-after);
  padding-left: 0;
}

.k-input[data-theme="field"] .k-input-icon > .k-dropdown {
  width: 100%;
  height: 100%;
}
.k-input[data-theme="field"] .k-input-icon-button {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.k-input[data-theme="field"] .k-number-input,
.k-input[data-theme="field"] .k-select-input,
.k-input[data-theme="field"] .k-text-input {
  padding: var(--field-input-padding);
  line-height: var(--field-input-line-height);
}

.k-input[data-theme="field"] .k-date-input .k-select-input,
.k-input[data-theme="field"] .k-time-input .k-select-input {
  padding-left: 0;
  padding-right: 0;
}

[dir="ltr"] .k-input[data-theme="field"] .k-date-input .k-select-input:first-child, 
[dir="ltr"] .k-input[data-theme="field"] .k-time-input .k-select-input:first-child {
  padding-left: var(--field-input-padding);
}
[dir="rtl"] .k-input[data-theme="field"] .k-date-input .k-select-input:first-child, 
[dir="rtl"] .k-input[data-theme="field"] .k-time-input .k-select-input:first-child {
  padding-right: var(--field-input-padding);
}

.k-input[data-theme="field"] .k-date-input .k-select-input:focus-within,
.k-input[data-theme="field"] .k-time-input .k-select-input:focus-within {
  color: var(--color-focus);
  font-weight: var(--font-bold);
}
.k-input[data-theme="field"].k-time-input .k-time-input-meridiem {
  padding-left: var(--field-input-padding);
}

/* Checkboxes & Radio Buttons */
.k-input[data-theme="field"][data-type=checkboxes] .k-checkboxes-input li,
.k-input[data-theme="field"][data-type=checkboxes] .k-radio-input li,
.k-input[data-theme="field"][data-type=radio] .k-checkboxes-input li,
.k-input[data-theme="field"][data-type=radio] .k-radio-input li {
  min-width: 0;
  overflow-wrap: break-word;
}

/* Checkboxes */
.k-input[data-theme="field"][data-type="checkboxes"] .k-input-before {
  border-right: 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-input-element + .k-input-after,
.k-input[data-theme="field"][data-type="checkboxes"] .k-input-element + .k-input-icon {
  border-left : 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-input-element {
  overflow: hidden;
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-checkboxes-input {
  display: grid;
  grid-template-columns: 1fr;
  margin-bottom: -1px;
  margin-right: -1px;
}
@media screen and (min-width: 65em) {
  .k-input[data-theme="field"][data-type="checkboxes"] .k-checkboxes-input {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-checkboxes-input li {
  border-right: 1px solid var(--color-background);
  border-bottom: 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-checkboxes-input label {
  display: block;
  line-height: var(--field-input-line-height);
  padding: var(--field-input-padding) var(--field-input-padding);
}
.k-input[data-theme="field"][data-type="checkboxes"] .k-checkbox-input-icon {
  top: calc((var(--field-input-height) - var(--field-input-font-size)) / 2);
  left: var(--field-input-padding);
  margin-top: 0px;
}

/* Radio */
.k-input[data-theme="field"][data-type="radio"] .k-input-before {
  border-right: 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="radio"] .k-input-element + .k-input-after,
.k-input[data-theme="field"][data-type="radio"] .k-input-element + .k-input-icon {
  border-left: 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="radio"] .k-input-element {
  overflow: hidden;
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input {
  display: grid;
  grid-template-columns: 1fr;
  margin-bottom: -1px;
  margin-right: -1px;
}
@media screen and (min-width: 65em) {
  .k-input[data-theme="field"][data-type="radio"] .k-radio-input {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input li {
  border-right: 1px solid var(--color-background);
  border-bottom: 1px solid var(--color-background);
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input label {
  display: block;
  flex-grow: 1;
  min-height: var(--field-input-height);
  line-height: var(--field-input-line-height);
  padding: calc((var(--field-input-height) - var(--field-input-line-height)) / 2) var(--field-input-padding);
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input label::before {
  top: calc((var(--field-input-height) - 1rem) / 2);
  left: var(--field-input-padding);
  margin-top: -1px;
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input .k-radio-input-info {
  display: block;
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  line-height: var(--field-input-line-height);
  padding-top: calc(var(--field-input-line-height) / 10);
}
.k-input[data-theme="field"][data-type="radio"] .k-radio-input .k-icon {
  width: var(--field-input-height);
  height: var(--field-input-height);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Range */
.k-input[data-theme="field"][data-type="range"]  .k-range-input {
  padding: var(--field-input-padding);
}

/* Select Boxes */
.k-input[data-theme="field"][data-type="select"] {
  position: relative;
}
.k-input[data-theme="field"][data-type="select"] .k-input-icon {
  position: absolute;
  top: 0;
  bottom: 0;
}
[dir="ltr"] .k-input[data-theme="field"][data-type="select"] .k-input-icon {
  right: 0;
}

[dir="rtl"] .k-input[data-theme="field"][data-type="select"] .k-input-icon {
  left: 0;
}

/* Tags */
.k-input[data-theme="field"][data-type="tags"] .k-tags-input {
  padding: .25rem .25rem 0 .25rem;
}
.k-input[data-theme="field"][data-type="tags"] .k-tag {
  margin-right: .25rem;
  margin-bottom: .25rem;
  height: 1.75rem;
  font-size: var(--text-sm);
}
.k-input[data-theme="field"][data-type="tags"] .k-tags-input input {
  font-size: var(--text-sm);
  padding: 0 .25rem;
  height: 1.75rem;
  line-height: 1;
  margin-bottom: .25rem;
}
.k-input[data-theme="field"][data-type="tags"] .k-tags-input .k-dropdown-content {
  top: calc(100% + .5rem + 2px);
}

/* Multiselect */
.k-input[data-theme="field"][data-type="multiselect"] {
  position: relative;
}
.k-input[data-theme="field"][data-type="multiselect"] .k-multiselect-input {
  padding: .25rem 2rem 0 .25rem;
  min-height: 2.25rem;
}
.k-input[data-theme="field"][data-type="multiselect"] .k-tag {
  margin-right: .25rem;
  margin-bottom: .25rem;
  height: 1.75rem;
  font-size: var(--text-sm);
}
.k-input[data-theme="field"][data-type="multiselect"] .k-input-icon {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

/* Textarea */
.k-input[data-theme="field"][data-type="textarea"]  .k-textarea-input-native {
  padding: .25rem var(--field-input-padding);
  line-height: 1.5rem;
}

/* Toggle */
.k-input[data-theme="field"][data-type="toggle"] .k-input-before {
  padding-right: calc(var(--field-input-padding) / 2);
}
.k-input[data-theme="field"][data-type="toggle"] .k-toggle-input {
  padding-left: var(--field-input-padding);
}
.k-input[data-theme="field"][data-type="toggle"] .k-toggle-input-label {
  padding: 0 var(--field-input-padding) 0 .75rem;
  line-height: var(--field-input-height);
}

</style>
