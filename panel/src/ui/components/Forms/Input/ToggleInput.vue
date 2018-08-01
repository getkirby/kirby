<template>
  <label class="k-toggle-input">
    <input
      ref="input"
      :checked="value"
      :disabled="disabled"
      :id="id"
      class="k-toggle-input-native"
      type="checkbox"
      v-on="listeners"
    >
    <span class="k-toggle-input-label" v-html="label" />
  </label>
</template>

<script>
import { required } from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    text: {
      type: [Array, String],
      default() {
        return ['off', 'on'];
      }
    },
    required: Boolean,
    value: Boolean,
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        change: (event) => this.onInput(event.target.checked),
        keydown: this.onEnter
      }
    }
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

<style lang="scss">
$toggle-background: $color-white;
$toggle-color: $color-light-grey;
$toggle-active-color: $color-dark;
$toggle-focus-color: $color-focus;
$toggle-height: 16px;

.k-toggle-input {
  display: flex;
  align-items: center;
}
.k-toggle-input-native {
  position: relative;
  height: $toggle-height;
  width: $toggle-height * 2;
  border-radius: $toggle-height;
  border: 2px solid $toggle-color;
  box-shadow: inset 0 0 0 2px $toggle-background, inset $toggle-height*-1 0px 0px 2px $toggle-background;
  background-color: $toggle-color;
  outline: 0;
  transition: all ease-in-out 0.1s;
  appearance: none;
  cursor: pointer;
  flex-shrink: 0;

  &:checked {
    border-color: $toggle-active-color;
    box-shadow: inset 0 0 0 2px $toggle-background, inset $toggle-height 0px 0px 2px $toggle-background;
    background-color: $toggle-active-color;
  }

  &:focus:checked {
    border: 2px solid $color-focus;
    background-color: $toggle-focus-color;
  }

  &::-ms-check {
    opacity: 0;
  }
}


.k-toggle-input-label {
  cursor: pointer;
  flex-grow: 1;
}
</style>
