<template>
  <ul
    :style="'--columns:' + columns"
    class="k-radio-input"
  >
    <li
      v-for="(option, index) in radios"
      :key="index"
    >
      <input
        :id="id + '-' + index"
        :value="option.value"
        :name="id"
        :checked="value === option.value"
        type="radio"
        class="k-radio-input-native"
        @change="onInput(option.value)"
      >
      <label :for="id + '-' + index">
        <template v-if="option.info">
          <span class="k-radio-input-text">{{ option.text }}</span>
          <span class="k-radio-input-info">{{ option.info }}</span>
        </template>
        <template v-else>
          {{ option.text }}
        </template>
      </label>
      <k-icon
        v-if="option.icon"
        :type="option.icon"
      />
    </li>
  </ul>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    columns: Number,
    disabled: Boolean,
    id: {
      type: [Number, String],
      default() {
        return this._uid;
      }
    },
    options: Array,
    required: Boolean,
    value: [String, Number, Boolean]
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
    }
  },
  computed: {
    radios() {
      return this.$helper.input.options(this.options);
    }
  },
  methods: {
    focus() {
      this.$el.querySelector("input").focus();
    },
    onInput(value) {
      this.$emit("input", value);
    },
    select() {
      this.focus();
    }
  },
}
</script>

<style lang="scss">
.k-radio-input {
  display: grid;
  grid-template-columns: 1fr;
}
@media screen and (min-width: $breakpoint-medium) {
  .k-radio-input {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
.k-radio-input li {
  position: relative;
  line-height: 1.5rem;
  padding-left: 1.75rem;
}
.k-radio-input input {
  position: absolute;
  width: 0;
  height: 0;
  appearance: none;
  opacity: 0;
}
.k-radio-input label {
  cursor: pointer;
  align-items: center;
}
.k-radio-input label::before {
  position: absolute;
  top: .25em;
  left: 0;
  content: "";
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  border: 2px solid $color-gray-500;
  box-shadow: $color-white 0 0 0 2px inset;
}
.k-radio-input input:checked + label::before {
  border-color: $color-black;
  background: $color-black;
}
.k-radio-input input:focus + label::before {
  border-color: $color-focus;
}
.k-radio-input input:focus:checked + label::before {
  background: $color-focus;
}

.k-radio-input-text {
  display: block;
}
</style>
