<template>
  <div
    :data-equalize="equalize"
    :data-show-text-label="labels"
    :style="'--options:' + options.length"
    class="k-toggles-input"
  >
    <ul>
      <li v-for="(option, index) in options" :key="index">
        <input
          :id="id + '-' + index"
          :value="option.value"
          :name="id"
          :checked="value === option.value"
          type="radio"
          @change="onInput(option.value)"
        />
        <label :for="id + '-' + index" :title="option.text">
          <k-icon v-if="option.icon" :type="option.icon" />
          <span v-if="labels" class="k-toggles-text">
            {{ option.text }}
          </span>
        </label>
      </li>
    </ul>
    <k-button
      v-if="value && reset && !required"
      :tooltip="$t('reset')"
      @click="onReset()"
    >
      <k-icon type="undo" />
    </k-button>
  </div>
</template>

<script>
import { autofocus, disabled, id, required } from "@/mixins/props.js";
import { requiredValidator } from "vuelidate/lib/validators";

export const props = {
  mixins: [autofocus, disabled, id, required],
  props: {
    options: Array,
    labels: Boolean,
    reset: Boolean,
    equalize: Boolean,
    value: [String, Number, Boolean]
  }
};

export default {
  inheritAttrs: false,
  mixins: [props],
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
      (
        this.$el.querySelector("input[checked]") ||
        this.$el.querySelector("input")
      ).focus();
    },
    onInput(value) {
      this.$emit("input", value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    select() {
      this.focus();
    },
    onReset() {
      this.$emit("reset");
    }
  },
  validations() {
    return {
      value: {
        required: this.required ? requiredValidator : true
      }
    };
  }
};
</script>

<style>
.k-toggles-input {
  --col-width: auto;
  display: inline-flex;
  line-height: 1;
  height: var(--field-input-height);
}

.k-toggles-input[data-equalize] {
  --col-width: 1fr;
}

.k-toggles-input ul {
  border: 1px solid var(--color-border);
  border-radius: var(--rounded-sm);
  display: inline-grid;
  grid-template-columns: repeat(var(--options), var(--col-width));
  overflow: hidden;
  text-align: center;
}

.k-toggles-field .k-input[data-invalid] ul {
  box-shadow: 0 0 3px 2px var(--color-negative-outline);
}

.k-toggles-input ul + .k-button {
  margin-left: 1rem;
}

.k-toggles-field .k-input[data-invalid]:focus-within {
  border: 0 !important;
  box-shadow: none !important;
}

.k-toggles-field .k-input[data-invalid]:focus-within ul {
  border: 1px solid var(--color-negative);
  box-shadow: 0 0 0 2px var(--color-negative-outline);
}

.k-toggles-input:focus-within ul {
  border: 1px solid var(--color-focus);
  box-shadow: 0 0 0 2px var(--color-focus-outline);
}

.k-toggles-input li {
  position: relative;
}

.k-toggles-input input {
  appearance: none;
  height: 0;
  opacity: 0;
  position: absolute;
  width: 0;
}

.k-toggles-input label {
  align-items: center;
  background: var(--color-white);
  cursor: pointer;
  display: flex;
  font-size: var(--text-sm);
  justify-content: center;
  line-height: 1.25rem;
  height: 100%;
  padding: 0.5rem 0.75rem;
}

.k-toggles-input li + li label {
  border-inline-start: 1px solid var(--color-border);
}

.k-toggles-input .k-icon + .k-toggles-text {
  margin-inline-start: 0.5rem;
}

.k-toggles-input input + label {
  color: var(--color-text);
}

.k-toggles-input input:checked + label {
  background: var(--color-text);
  color: var(--color-white);
}

.k-toggles-input:focus-within input:checked + label {
  color: var(--color-blue-300);
}

.k-toggles-input .k-button {
  font-size: var(--text-sm);
}
</style>
