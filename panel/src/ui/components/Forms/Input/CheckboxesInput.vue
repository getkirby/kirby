<template>
  <ul :style="'--columns:' + columns" class="k-checkboxes-input">
    <li v-for="(option, index) in options" :key="index">
      <k-checkbox-input
        :id="id + '-' + index"
        :label="option.text"
        :value="selected.indexOf(option.value) !== -1"
        @input="onInput(option.value, $event)"
      />
    </li>
  </ul>
</template>

<script>
import { required, minLength, maxLength } from "vuelidate/lib/validators";

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
      type: Array,
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
    },
    selected() {
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
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.focus();
    },
    valueToArray(value) {
      return Array.isArray(value) ? value : String(value).split(",");
    },
  },
  validations() {
    return {
      selected: {
        required: this.required ? required : true,
        min: this.min ? minLength(this.min) : true,
        max: this.max ? maxLength(this.max) : true,
      }
    };
  }
}

</script>
