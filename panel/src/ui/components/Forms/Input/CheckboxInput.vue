<template>
  <label class="kirby-checkbox-input">
    <input
      ref="input"
      :checked="value"
      :disabled="disabled"
      :id="id"
      class="kirby-checkbox-input-native"
      type="checkbox"
      v-on="listeners"
    >
    <span class="kirby-checkbox-input-icon" aria-hidden="true">
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
    <span class="kirby-checkbox-input-label" v-html="label" />
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
    label: String,
    required: Boolean,
    value: Boolean,
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        change: (event) => this.onChange(event.target.checked)
      }
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
    onChange(checked) {
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

.kirby-checkbox-input {
  position: relative;
  cursor: pointer;
}
.kirby-checkbox-input-native {
  position: absolute;
  appearance: none;
  width: 0;
  height: 0;
}
.kirby-checkbox-input-label {
  display: block;
  padding-left: 1.75rem;
}
.kirby-checkbox-input-icon {
  position: absolute;
  left: 0;
  width: 1rem;
  height: 1rem;
  border: 2px solid $color-light-grey;
}
.kirby-checkbox-input-icon svg {
  position: absolute;
  width: 12px;
  height: 12px;
  display: none;
}
.kirby-checkbox-input-icon path {
  stroke: $color-white;
}
.kirby-checkbox-input-native:checked + .kirby-checkbox-input-icon {
  border-color: $color-dark;
  background: $color-dark;
}
.kirby-checkbox-input-native:checked + .kirby-checkbox-input-icon svg {
  display: block;
}
.kirby-checkbox-input-native:focus + .kirby-checkbox-input-icon {
  border-color: $color-focus-border;
}
.kirby-checkbox-input-native:focus:checked + .kirby-checkbox-input-icon {
  background: $color-focus;
}

</style>
