<template>
  <span
    :data-disabled="disabled"
    :data-empty="selected === ''"
    class="k-select-input"
  >
    <select
      :id="id"
      ref="input"
      :autofocus="autofocus"
      :aria-label="ariaLabel"
      :disabled="disabled"
      :name="name"
      :required="required"
      :value="selected"
      class="k-select-input-native"
      v-on="listeners"
    >
      <option
        v-if="hasEmptyOption"
        :disabled="required"
        value=""
      >
        {{ emptyOption }}
      </option>

      <template v-for="option in selectOptions">
        <!-- grouped -->
        <optgroup
          v-if="hasGroups"
          :key="option.group"
          :label="option.group"
        >
          <option
            v-for="opt in option.options"
            :key="opt.value"
            :disabled="opt.disabled"
            :value="opt.value"
          >
            {{ opt.text }}
          </option>
        </optgroup>

        <!-- regular -->
        <option
          v-else
          :key="option.value"
          :disabled="option.disabled"
          :value="option.value"
        >
          {{ option.text }}
        </option>
      </template>
    </select>
    {{ label }}
  </span>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    ariaLabel: String,
    default: String,
    disabled: Boolean,
    empty: {
      type: [Boolean, String],
      default: true
    },
    id: [Number, String],
    name: [Number, String],
    placeholder: String,
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
        change: (event) => this.onInput(event.target.value),
        input: () => {}
      }
    };
  },
  computed: {
    emptyOption() {
      return this.placeholder || "—";
    },
    hasEmptyOption() {
      if (this.empty === false) {
        return false;
      }

      return !(this.required && this.default);
    },
    hasGroups() {
      if (!this.options[0]) {
        return false;
      }

      return this.options[0].hasOwnProperty("group") === true &&
             this.options[0].hasOwnProperty("options") === true;
    },
    label() {
      const label = this.text(this.selected);

      if (this.selected === "" || this.selected === null || label === null) {
        return this.emptyOption;
      }

      return label;
    },
    selectOptions() {
      if (this.hasGroups) {
        return this.options.map(group => {
          group.options = this.$helper.input.options(group.options);
          return group;
        });
      }

      return this.$helper.input.options(this.options);
    }
  },
  watch: {
    value(value) {
      this.selected = value;
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
    onClick(event) {
      event.stopPropagation();
      this.$emit("click", event);
    },
    onInput(value) {
      this.selected = value;
      this.$emit("input", this.selected);
    },
    select() {
      this.focus();
    },
    text(value, options = this.selectOptions) {
      for (let i = 0; i < options.length; i++) {
        // regular list
        if (options[i].value == value) {
          return options[i].text;
        }

        // grouped options list
        if (options[i].options) {
          const text = this.text(value, options[i].options);
          if (text) {
            return text;
          }
        }
      }
    }
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
.k-select-input-native {
  font-weight: $font-normal;
}

/** Theming **/
.k-input[data-theme="field"] {
  .k-select-input {
    padding: $field-input-padding;
    line-height: $field-input-line-height;
  }

  &[data-type="select"] {
    position: relative;

    .k-input-icon {
      position: absolute;
      top: 0;
      bottom: 0;

      [dir="ltr"] & {
        right: 0;
      }

      [dir="rtl"] & {
        left: 0;
      }
    }
  }
}
</style>
