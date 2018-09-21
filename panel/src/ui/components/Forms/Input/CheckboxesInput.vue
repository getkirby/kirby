<template>
  <ul class="k-checkboxes-input">
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
      selected: this.value
    }
  },
  watch: {
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

<style lang="scss">

/* Field Theme */
.k-checkboxes-input .k-input-element {
  overflow: hidden;
}
.k-checkboxes-input[data-theme="field"] ul {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: -1px;
  margin-right: -1px;
}
.k-checkboxes-input[data-theme="field"] li {
  flex-grow: 1;
  flex-basis: 100%;
  flex-shrink: 0;
  border-right: 1px solid $color-background;
  border-bottom: 1px solid $color-background;
  @media screen and (min-width: $breakpoint-medium) {
    flex-basis: 50%;
  }
}
.k-checkboxes-input[data-theme="field"] label {
  display: block;
  min-height: $field-input-height;
  line-height: 2rem;
  padding: 0 $field-input-padding;
}
.k-checkboxes-input[data-theme="field"] .k-checkbox-icon {
  top: .5rem;
  left: .5rem;
}
</style>
