<template>
  <label
    class="k-checkbox-input"
    @click.stop
  >
    <input
      :id="id"
      ref="input"
      :checked="value"
      :disabled="disabled"
      class="k-checkbox-input-native"
      type="checkbox"
      @change="onChange($event.target.checked)"
    >
    <span
      class="k-checkbox-input-icon"
      aria-hidden="true"
    >
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
    <span
      class="k-checkbox-input-label"
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
    label: String,
    required: Boolean,
    value: Boolean,
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
    onChange(checked) {
      this.$emit("input", checked);
    },
    select() {
      this.focus();
    }
  }
}
</script>

<style lang="scss">

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
  padding-left: 1.75rem;
}
.k-checkbox-input-icon {
  position: absolute;
  left: 0;
  width: 16px;
  height: 16px;
  border: 2px solid $color-gray-500;
  border-radius: $rounded-sm;
  box-shadow: $shadow-inset;
}
.k-checkbox-input-icon svg {
  position: absolute;
  top: 0;
  left: 0;
  width: 12px;
  height: 12px;
  display: none;
}
.k-checkbox-input-icon path {
  stroke: $color-white;
}
.k-checkbox-input-native:checked + .k-checkbox-input-icon {
  border-color: $color-black;
  background: $color-black;
}
.k-checkbox-input-native:checked + .k-checkbox-input-icon svg {
  display: block;
}
.k-checkbox-input-native:focus + .k-checkbox-input-icon {
  border-color: $color-focus;
}
.k-checkbox-input-native:focus:checked + .k-checkbox-input-icon {
  background: $color-focus;
}

</style>
