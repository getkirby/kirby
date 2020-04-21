<template>
  <ul
    :style="'--columns:' + columns"
    class="k-checkboxes-input"
  >
    <li
      v-for="(option, index) in checkboxes"
      :key="index"
    >
      <k-checkbox-input
        :disabled="disabled"
        :id="id + '-' + index"
        :label="option.text"
        :value="selected.indexOf(option.value) !== -1"
        @input="onInput(option.value, $event)"
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
    max: Number,
    min: Number,
    options: Array,
    required: Boolean,
    value: {
      type: [Array, Object],
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      selected: this.valueToArray(this.value)
    }
  },
  watch: {
    value(value) {
      this.selected = this.valueToArray(value);
    }
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
    }
  },
  computed: {
    checkboxes() {
      return this.$helper.input.options(this.options);
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
    },
  }
}
</script>

<style lang="scss">
.k-checkboxes-input {
  display: grid;
  grid-template-columns: 1fr;
}

@media screen and (min-width: $breakpoint-medium) {
  .k-checkboxes-input {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}

</style>
