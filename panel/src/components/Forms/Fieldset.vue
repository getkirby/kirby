<template>
  <fieldset class="k-fieldset">
    <k-grid>
      <template v-for="(field, fieldName) in fields">
        <k-column
          v-if="field.type !== 'hidden' && meetsCondition(field)"
          :key="field.signature"
          :width="field.width"
        >
          <k-error-boundary>
            <component
              v-if="hasFieldType(field.type)"
              :is="'k-' + field.type + '-field'"
              :ref="fieldName"
              v-model="value[fieldName]"
              :name="fieldName"
              :novalidate="novalidate"
              :disabled="disabled || field.disabled"
              v-bind="field"
              @input="$emit('input', value, field, fieldName)"
              @focus="$emit('focus', $event, field, fieldName)"
              @invalid="($invalid, $v) => onInvalid($invalid, $v, field, fieldName)"
              @submit="$emit('submit', $event, field, fieldName)"
            />
            <k-box v-else theme="negative">
              <k-text size="small">
                The field type <strong>"{{ fieldName }}"</strong> does not exist
              </k-text>
            </k-box>
          </k-error-boundary>
        </k-column>
      </template>
    </k-grid>
  </fieldset>
</template>

<script>
export default {
  props: {
    config: Object,
    disabled: Boolean,
    fields: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    novalidate: {
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
      if (name) {
        if (
          this.hasField(name) &&
          typeof this.$refs[name][0].focus === "function"
        ) {
          this.$refs[name][0].focus();
        }
        return;
      }

      const key = Object.keys(this.$refs)[0];
      this.focus(key);
    },
    hasFieldType(type) {
      return this.$helper.isComponent(`k-${type}-field`);
    },
    hasField(name) {
      return this.$refs[name] && this.$refs[name][0];
    },
    meetsCondition(field) {

      if (!field.when) {
        return true;
      }

      let result = true;

      Object.keys(field.when).forEach(key => {
        const value     = this.value[key.toLowerCase()];
        const condition = field.when[key];

        if (value !== condition) {
          result = false;
        }
      });

      return result;

    },
    onInvalid($invalid, $v, field, fieldName) {
      this.errors[fieldName] = $v;
      this.$emit("invalid", this.errors);
    },
    hasErrors() {
      return Object.keys(this.errors).length;
    }
  }
};
</script>

<style lang="scss">
.k-fieldset {
  border: 0;
}
.k-fieldset .k-grid {
  grid-row-gap: 2.25rem;
}

@media screen and (min-width: $breakpoint-small) {
  .k-fieldset .k-grid {
    grid-column-gap: 1.5rem;
  }
}

/* Switch off the grid in narrow sections */
.k-sections > .k-column[data-width="1/3"] .k-fieldset .k-grid,
.k-sections > .k-column[data-width="1/4"] .k-fieldset .k-grid {
  grid-template-columns: repeat(1, 1fr);

  .k-column {
    grid-column-start: initial;
  }
}
</style>
