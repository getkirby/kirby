<template>
  <label :data-disabled="disabled" class="k-toggle-input">
    <input
      :id="id"
      ref="input"
      :checked="value"
      :disabled="disabled"
      class="k-toggle-input-native"
      type="checkbox"
      @change="onInput($event.target.checked)"
    >
    <span class="k-toggle-input-label" v-text="label" />
  </label>
</template>

<script>
import { required } from "vuelidate/lib/validators";

/**
 * @example <k-input v-model="toggle" name="toggle" type="toggle" />
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    /**
     * The text to display next to the toggle. This can either be a string 
     * that doesn't change when the toggle switches. Or an array with the 
     * first value for the `false` text and the second value for 
     * the `true` text.
     */
    text: {
      type: [Array, String],
      default() {
        return [
          this.$t("off"),
          this.$t("on"),
        ];
      }
    },
    required: Boolean,
    value: Boolean,
  },
  computed: {
    label() {
      if (Array.isArray(this.text)) {
        return this.value ? this.text[1] : this.text[0];
      }

      return this.text;
    }
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
    onEnter(e) {
      if (e.key === "Enter") {
        this.$refs.input.click();
      }
    },
    onInput(checked) {
      this.$emit("input", checked);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.$refs.input.focus();
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
.k-toggle-input {
  --toggle-background: var(--color-white);
  --toggle-color: var(--color-gray-500);
  --toggle-active-color: var(--color-gray-900);
  --toggle-focus-color: var(--color-focus);
  --toggle-height: 16px;

  display: flex;
  align-items: center;
}
.k-toggle-input-native {
  position: relative;
  height: var(--toggle-height);
  width: calc(var(--toggle-height) * 2);
  border-radius: var(--toggle-height);
  border: 2px solid var(--toggle-color);
  box-shadow: inset 0 0 0 2px var(--toggle-background), inset calc(var(--toggle-height) * -1) 0px 0px 2px var(--toggle-background);
  background-color: var(--toggle-color);
  outline: 0;
  transition: all ease-in-out .1s;
  appearance: none;
  cursor: pointer;
  flex-shrink: 0;
}
.k-toggle-input-native:checked {
  border-color: var(--toggle-active-color);
  box-shadow: inset 0 0 0 2px var(--toggle-background), inset var(--toggle-height) 0px 0px 2px var(--toggle-background);
  background-color: var(--toggle-active-color);
}

.k-toggle-input-native[disabled] {
  border-color: var(--color-border);
  box-shadow: inset 0 0 0 2px var(--color-background), inset calc(var(--toggle-height) * -1) 0px 0px 2px var(--color-background);
  background-color: var(--color-border);
}

.k-toggle-input-native[disabled]:checked {
  box-shadow: inset 0 0 0 2px var(--color-background), inset var(--toggle-height) 0px 0px 2px var(--color-background);
}

.k-toggle-input-native:focus:checked {
  border: 2px solid var(--color-focus);
  background-color: var(--toggle-focus-color);
}

.k-toggle-input-native::-ms-check {
  opacity: 0;
}

.k-toggle-input-label {
  cursor: pointer;
  flex-grow: 1;
}
</style>
