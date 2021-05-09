<template>
  <input
    ref="input"
    v-bind="{
      autocomplete,
      autofocus,
      disabled,
      id,
      minlength,
      name,
      pattern,
      placeholder,
      required,
      spellcheck,
      type,
      value
    }"
    :dir="direction"
    class="k-text-input"
    v-on="listeners"
  >
</template>

<script>
import direction from "@/helpers/direction.js";

import {
  autofocus,
  disabled,
  id,
  name,
  required
} from "@/mixins/props.js"

export const props = {
  mixins: [
    autofocus,
    disabled,
    id,
    name,
    required
  ],
  props: {
    autocomplete: {
      type: [Boolean, String],
      default: "off"
    },
    maxlength: Number,
    minlength: Number,
    pattern: String,
    placeholder: String,
    preselect: Boolean,
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    type: {
      type: String,
      default: "text"
    },
    value: String
  }
}

/**
 * @example <k-input v-model="text" name="text" type="text" />
 */
export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      listeners: {
        ...this.$listeners,
        input: event => this.onInput(event.target.value)
      }
    };
  },
  computed: {
    direction() {
      return direction(this);
    }
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
    }

    if (this.$props.preselect) {
      this.select();
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onInput(value) {
      this.$emit("input", value);
    },
    select() {
      this.$refs.input.select();
    }
  }
};
</script>

<style >
.k-text-input {
  width: 100%;
  border: 0;
  background: none;
  font: inherit;
  color: inherit;
}
.k-text-input::placeholder {
  color: var(--color-gray-500);
}
.k-text-input:focus {
  outline: 0;
}
.k-text-input:invalid {
  box-shadow: none;
  outline: 0;
}
</style>
