<template>
  <ul :style="'--columns:' + columns" class="k-checkboxes-input">
    <li v-for="(option, index) in options" :key="index">
      <k-checkbox-input
        :id="id + '-' + index"
        :label="option.text"
        :value="selected.indexOf(option.value) !== -1"
        @input="onInput(option.value, $event)"
      />
    </li>
  </ul>
</template>

<script>
import { autofocus, disabled, id, required } from "@/mixins/props.js";

import {
  required as validateRequired,
  minLength as validateMinLength,
  maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
  mixins: [autofocus, disabled, id, required],
  props: {
    columns: Number,
    max: Number,
    min: Number,
    options: Array,
    /**
     * The value for the input should be provided as array. Each value in the array corresponds with the value in the options. If you provide a string, the string will be split by comma.
     */
    value: {
      type: [Array, Object],
      default() {
        return [];
      }
    }
  }
};

export default {
  mixins: [props],
  inheritAttrs: false,
  data() {
    return {
      selected: this.valueToArray(this.value)
    };
  },
  watch: {
    value(value) {
      this.selected = this.valueToArray(value);
    },
    selected() {
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
      this.$el.querySelector("input").focus();
    },
    onInput(key, value) {
      if (value === true) {
        this.selected.push(key);
      } else {
        const index = this.selected.indexOf(key);
        if (index !== -1) {
          this.selected.splice(index, 1);
        }
      }
      this.$emit("input", this.selected);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.focus();
    },
    valueToArray(value) {
      if (Array.isArray(value) === true) {
        return value;
      }

      if (typeof value === "string") {
        return String(value).split(",");
      }

      if (typeof value === "object") {
        return Object.values(value);
      }
    }
  },
  validations() {
    return {
      selected: {
        required: this.required ? validateRequired : true,
        min: this.min ? validateMinLength(this.min) : true,
        max: this.max ? validateMaxLength(this.max) : true
      }
    };
  }
};
</script>
