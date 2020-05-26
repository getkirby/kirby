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
      <label
        :for="id + '-' + index"
        class="flex items-start cursor-pointer"
      >
        <k-icon
          v-bind="toggleState(option)"
          class="k-radio-input-toggle"
        />
        <div class="flex-grow">
          <template v-if="option.info">
            <span class="k-radio-input-text block">{{ option.text }}</span>
            <span class="k-radio-input-info">{{ option.info }}</span>
          </template>
          <template v-else>
            {{ option.text }}
          </template>
        </div>
        <k-icon
          v-if="option.icon"
          :type="option.icon"
          :color="value === option.value ? (option.color || 'black') : 'gray-light'"
          class="k-radio-input-icon"
        />
      </label>
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
  computed: {
    radios() {
      return this.$helper.input.options(this.options);
    }
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
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
    },
    toggleState(option) {
      if (this.value === option.value) {
        return {
          type: "circle-filled",
          color: "black"
        };
      }

      return {
        type: "circle-outline",
        color: "gray-light"
      };
    }
  },
}
</script>

<style lang="scss">
$input-line-height: 1.5rem;

.k-radio-input {
  display: grid;
  grid-template-columns: 1fr;
}
@media screen and (min-width: $breakpoint-md) {
  .k-radio-input {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
.k-radio-input li {
  position: relative;
  line-height: $input-line-height;
}
.k-radio-input input {
  position: absolute;
  width: 0;
  height: 0;
  appearance: none;
  opacity: 0;
}

.k-radio-input input:focus:checked + label .k-radio-input-toggle {
  color: $color-focus;
}

.k-radio-input-toggle {
  height: $input-line-height;
  padding-right: .75rem;
}
.k-radio-input-info {
  display: block;
  font-size: $text-sm;
  color: $color-gray-700;
}
.k-radio-input-icon {
  height: $input-line-height;
  justify-self: flex-end;
  padding-left: .75rem;
}

/** Theming **/
.k-input[data-theme="field"][data-type="radio"] {
  margin-bottom: -1px;
  margin-right: -1px;

  .k-input-before {
    border-right: 1px solid $color-background;
  }
  .k-input-after {
    border-left : 1px solid $color-background;
  }
  li {
    border-right: 1px solid $color-background;
    border-bottom: 1px solid $color-background;
    min-width: 0;
    overflow-wrap: break-word;
  }
  label {
    min-height: $field-input-height;
    line-height: $field-input-line-height;
    padding: (($field-input-height - $field-input-line-height) / 2) $field-input-padding;
  }
  .k-radio-input-toggle,
  .k-radio-input-icon  {
    height: $field-input-line-height;
  }
  .k-radio-input-info {
    line-height: $field-input-line-height;
    padding-top: $field-input-line-height / 10;
  }
}
</style>
