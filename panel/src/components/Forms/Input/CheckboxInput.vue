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
    />
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
    <!-- eslint-disable-next-line vue/no-v-html -->
    <span class="k-checkbox-input-label" v-html="label" />
  </label>
</template>

<script>
import { autofocus, disabled, id, label, required } from "@/mixins/props.js";

import { required as validateRequired } from "vuelidate/lib/validators";

/**
 *
 * @example <k-input v-model="checkbox" type="checkbox" />
 */
export default {
  mixins: [autofocus, disabled, id, label, required],
  inheritAttrs: false,
  props: {
    value: Boolean
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
        required: this.required ? validateRequired : true
      }
    };
  }
};
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
  padding-inline-start: 1.75rem;
}
.k-checkbox-input-icon {
  position: absolute;
  inset-inline-start: 0;
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
[data-disabled="true"]
  .k-checkbox-input-native:checked
  + .k-checkbox-input-icon {
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
