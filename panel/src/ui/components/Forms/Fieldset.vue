<template>
  <fieldset class="kirby-fieldset">
    <kirby-grid>
      <kirby-column v-for="(field, fieldName) in fields" :key="fieldName" :width="field.width">
        <component
          v-if="hasFieldType(field.type)"
          :is="'kirby-' + field.type + '-field'"
          :name="fieldName"
          :ref="fieldName"
          :validate="validate"
          :disabled="disabled || field.disabled"
          v-bind="field"
          v-model="value[fieldName]"
          @input="$emit('input', value, field, fieldName)"
          @focus="$emit('focus', $event, field, fieldName)"
          @invalid="($invalid, $v) => onInvalid($invalid, $v, field, fieldName)"
          @submit="$emit('submit', $event, field, fieldName)"
        />
        <kirby-box v-else theme="negative">
          <kirby-text size="small" align="center">
            The field type <strong>"{{ fieldName }}"</strong> does not exist
          </kirby-text>
        </kirby-box>
      </kirby-column>
    </kirby-grid>
  </fieldset>
</template>

<script>
import Vue from "vue";

export default {
  props: {
    disabled: Boolean,
    fields: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    validate: {
      type: Boolean,
      default: false
    },
    value: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  data() {
    return {
      errors: {}
    };
  },
  methods: {
    focus(name) {
      if (name && this.hasField(name) && typeof this.$refs[name][0].focus === "function") {
        this.$refs[name][0].focus();
        return;
      }

      const key = Object.keys(this.$refs)[0];
      this.focus(key);
    },
    hasFieldType(type) {
      return Vue.options.components["kirby-" + type + "-field"];
    },
    hasField(name) {
      return this.$refs[name] && this.$refs[name][0];
    },
    onInvalid($invalid, $v, field, fieldName) {
      this.errors[fieldName] = $v;
      this.$emit("invalid", this.errors);
    }
  }
};
</script>

<style lang="scss">
.kirby-fieldset {
  border: 0;
}
.kirby-fieldset .kirby-grid {
  grid-column-gap: 1.5rem;
  grid-row-gap: 2.25rem;
}
</style>
