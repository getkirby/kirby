<template>
  <label :data-disabled="disabled" :data-empty="selected === ''" class="k-select-input">
    <select
      ref="input"
      v-bind="{
        autofocus,
        disabled,
        id,
        name,
        required
      }"
      v-model="selected"
      :disabled="disabled"
      class="k-select-input-native"
      v-on="listeners"
    >
      <option v-if="empty !== false" :value="null">{{ empty }}</option>
      <option
        v-for="option in options"
        :disabled="option.disabled"
        :key="option.value"
        :value="option.value"
      >
        {{ option.text }}
      </option>
    </select>
    {{ label }}
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
    name: [Number, String],
    placeholder: String,
    empty: {
      type: [String, Boolean],
      default: "—"
    },
    options: {
      type: Array,
      default: () => {
        return [];
      }
    },
    required: Boolean,
    value: {
      type: [String, Number, Boolean],
      default: ""
    }
  },
  data() {
    return {
      selected: this.value,
      listeners: {
        ...this.$listeners,
        click: (event) => this.onClick(event),
        input: (event) => this.onInput(event.target.value),
      }
    };
  },
  computed: {
    label() {
      const label = this.text(this.selected);

      if (this.selected === "" || this.selected === null || label === null) {
        return this.placeholder || "—";
      }

      return label;
    }
  },
  watch: {
    value(value) {
      this.selected = value;
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
    onClick(event) {
      event.stopPropagation();
      this.$emit("click", event);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onInput(value) {
      this.selected = value;
      this.$emit("input", this.selected);
    },
    select() {
      this.focus();
    },
    text(value) {
      let text = null;
      this.options.forEach(option => {
        if (option.value == value) {
          text = option.text;
        }
      });
      return text;
    }
  },
  validations() {
    return {
      selected: {
        required: this.required ? required : true,
      }
    };
  }
}
</script>

<style lang="scss">
.k-select-input {
  position: relative;
  display: block;
  cursor: pointer;
  overflow: hidden;
}
.k-select-input-native {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  opacity: 0;
  width: 100%;
  font: inherit;
  z-index: 1;
  cursor: pointer;
  appearance: none;
}
.k-select-input-native[disabled] {
  cursor: default;
}
</style>
