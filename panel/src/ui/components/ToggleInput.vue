<template>
  <label
    :data-disabled="disabled"
    class="k-toggle-input flex items-center"
  >
    <input
      :id="id"
      ref="input"
      :checked="checked"
      :disabled="disabled"
      class="k-toggle-input-native relative cursor-pointer"
      type="checkbox"
      @change="onInput($event.target.checked)"
    >
    <span
      v-if="text !== false"
      class="k-toggle-input-label cursor-pointer"
      v-html="label"
    />
  </label>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    disabled: Boolean,
    id: [Number, String],
    text: {
      type: [Array, Boolean, String],
      default() {
        return [
          this.$t("off"),
          this.$t("on"),
        ];
      }
    },
    required: Boolean,
    value: [Boolean, String, Number],
  },
  computed: {
    checked() {
      if (this.value == "") {
        return false;
      }

      return this.value == true;
    },
    label() {
      if (Array.isArray(this.text)) {
        return this.value ? this.text[1] : this.text[0];
      }

      return this.text;
    }
  },
  mounted() {
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
    select() {
      this.$refs.input.focus();
    }
  }
}
</script>

<style lang="scss">
$toggle-background: $color-white;
$toggle-color: $color-border;
$toggle-active-color: $color-black;
$toggle-focus-color: $color-focus;
$toggle-height: 16px;

.k-toggle-input-native {
  height: $toggle-height;
  width: $toggle-height * 2;
  border-radius: $toggle-height;
  border: 2px solid $toggle-color;
  box-shadow: inset 0 0 0 2px $toggle-background, inset $toggle-height*-1 0px 0px 2px $toggle-background;
  background-color: $toggle-color;
  outline: 0;
  transition: all ease-in-out 0.1s;
  appearance: none;
  flex-shrink: 0;

  &:checked {
    border-color: $toggle-active-color;
    box-shadow: inset 0 0 0 2px $toggle-background, inset $toggle-height 0px 0px 2px $toggle-background;
    background-color: $toggle-active-color;
  }

  &[disabled] {
    border-color: $color-border;
    box-shadow: inset 0 0 0 2px $color-background, inset $toggle-height*-1 0px 0px 2px $color-background;
    background-color: $color-border;
  }

  &[disabled]:checked {
    box-shadow: inset 0 0 0 2px $color-background, inset $toggle-height 0px 0px 2px $color-background;
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
  flex-grow: 1;
  padding-left: .75rem;
}

/** Theming **/
.k-input[data-theme="field"][data-type="toggle"] {
  .k-input-before {
    padding-right: $field-input-padding / 2;
  }
  .k-toggle-input {
    padding-left: $field-input-padding;
  }
  .k-toggle-input-label {
    padding: 0 $field-input-padding 0 .75rem;
    line-height: $field-input-height;
  }
}
</style>
