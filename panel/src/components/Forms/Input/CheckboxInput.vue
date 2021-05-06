<template>
  <label class="k-checkbox-input" @click.stop>
    <input
      :id="id"
      ref="input"
      :checked="value"
      :disabled="disabled"
      class="k-checkbox-input-native"
      type="checkbox"
      @change="onChange($event.target.checked)"
    >
    <span class="k-checkbox-input-icon" aria-hidden="true">
      <svg
        width="12"
        height="10"
        viewBox="0 0 12 10"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M1 5l3.3 3L11 1"
          stroke-width="2"
          fill="none"
          fill-rule="evenodd"
        />
      </svg>
    </span>
    <span class="k-checkbox-input-label" v-text="label" />
  </label>
</template>

<script>
import { required } from "vuelidate/lib/validators";

/**
 * 
 * @example <k-input v-model="checkbox" type="checkbox" />
 */
export default {
  inheritAttrs: false,
  props: {
    /**
     * If true, the input will be instantly focused when the form is created
     */
    autofocus: {
      type: Boolean,
      default: false
    },
    /**
     * If true, the input is disabled and cannot be filled in or edited
     */
    disabled: {
      type: Boolean,
      default: false
    },
    id: [Number, String],
    label: String,
    /**
     * If true, the input must not be empty
     */
    required: {
      type: Boolean,
      default: false
    },
    value: Boolean,
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
    onChange(checked) {
      /**
       * The input event is triggered when the value changes.
       * @event input
       * @property {boolean} checked
       */
      this.$emit("input", checked);
    },
    onInvalid() {
      /**
       * The invalid event is triggered when the input validation fails. This can be used to react on errors immediately.
       * @event invalid
       */
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.focus();
    }
  },
  validations() {
    return {
      value: {
        required: this.required ? required : true,
      }
    }
  }
}
</script>

<style>

.k-checkbox-input {
  position: relative;
  cursor: pointer;
}
.k-checkbox-input-native {
  position: absolute;
  appearance: none;
  width: 0;
  height: 0;
  opacity: 0;
}
.k-checkbox-input-label {
  display: block;
  padding-left: 1.75rem;
}
.k-checkbox-input-icon {
  position: absolute;
  left: 0;
  width: 1rem;
  height: 1rem;
  border: 2px solid var(--color-gray-500);
}
.k-checkbox-input-icon svg {
  position: absolute;
  width: 12px;
  height: 12px;
  display: none;
}
.k-checkbox-input-icon path {
  stroke: var(--color-white);
}
.k-checkbox-input-native:checked + .k-checkbox-input-icon {
  border-color: var(--color-gray-900);
  background: var(--color-gray-900);
}
[data-disabled] .k-checkbox-input-native:checked + .k-checkbox-input-icon {
  border-color: var(--color-gray-600);
  background: var(--color-gray-600);
}
.k-checkbox-input-native:checked + .k-checkbox-input-icon svg {
  display: block;
}
.k-checkbox-input-native:focus + .k-checkbox-input-icon {
  border-color: var(--color-blue-600);
}
.k-checkbox-input-native:focus:checked + .k-checkbox-input-icon {
  background: var(--color-focus);
}

</style>
