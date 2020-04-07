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
    class="k-text-input"
    v-on="listeners"
  >
</template>

<script>
export default {
  inheritAttrs: false,
  class: "k-text-input",
  props: {
    autocomplete: {
      type: [Boolean, String],
      default: "off"
    },
    autofocus: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    id: [Number, String],
    maxlength: Number,
    minlength: Number,
    name: [Number, String],
    pattern: String,
    placeholder: String,
    preselect: Boolean,
    required: {
      type: Boolean,
      default: false,
    },
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    type: {
      type: String,
      default: "text"
    },
    value: String
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        input: event => this.onInput(event.target.value)
      }
    };
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

<style lang="scss">
.k-text-input {
  width: 100%;
  border: 0;
  background: none;
  font: inherit;
  color: inherit;
}
.k-text-input::placeholder {
  color: $color-light-grey;
}
.k-text-input:focus {
  outline: 0;
}
.k-text-input:invalid {
  box-shadow: none;
  outline: 0;
}
</style>
