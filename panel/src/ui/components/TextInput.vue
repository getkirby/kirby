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
    }"
    :value="sanitize(value)"
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
    placeholder: [Boolean, String],
    preselect: Boolean,
    required: {
      type: Boolean,
      default: false,
    },
    slug: {
      type: [Boolean, String],
      default: false
    },
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    trim: {
      type: [Boolean, String],
      default: false
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
      },
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
    sanitize(value) {
      if (this.trim === true && value) {
        value = value.trim();
      }

      if (this.slug) {
        const allowed = typeof this.slug === "string" ? this.slug : "";

        // TODO: add global rules
        value = this.$helper.slug(value, [], allowed);
      }

      return value;
    },
    onInput(value) {
      this.$emit("input", this.sanitize(value));
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
  color: $color-placeholder;
}
.k-text-input:focus {
  outline: 0;
}
.k-text-input:invalid {
  box-shadow: none;
  outline: 0;
}

/** Theming **/
.k-input[data-theme="field"] {
  .k-text-input {
    padding: $field-input-padding;
    line-height: $field-input-line-height;
  }
}
</style>
