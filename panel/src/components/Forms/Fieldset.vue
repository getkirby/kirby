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
            <!-- @event input Triggered whenever any field value changes -->
            <!-- @event focus Triggered whenever any field is focused -->
            <!-- @event submit Triggered whenever any field triggers submit -->
            <!-- eslint-disable vue/no-mutating-props -->
            <component
              :is="'k-' + field.type + '-field'"
              v-if="hasFieldType(field.type)"
              :ref="fieldName"
              v-model="value[fieldName]"
              :form-data="value"
              :name="fieldName"
              :novalidate="novalidate"
              :disabled="disabled || field.disabled"
              v-bind="field"
              @input="$emit('input', value, field, fieldName)"
              @focus="$emit('focus', $event, field, fieldName)"
              @invalid="
                ($invalid, $v) => onInvalid($invalid, $v, field, fieldName)
              "
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
/**
 * The Fieldset component is a wrapper around manual field component creation. You simply pass it an fields object and a v-model and all field components will automatically be created including a nice field grid. This is the ideal starting point if you want an easy way to create fields without having to deal with a full form element.
 */
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
    /**
     * If `true`, form fields won't show their validation status on the fly.
     */
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
    /**
     * Focus a specific field in the fieldset or the first one if no name is given
     * @public
     * @param  {string} name field name to focus
     */
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
    /**
     * Check if a particular field type exists
     * @public
     * @param {string} type field type
     */
    hasFieldType(type) {
      return this.$helper.isComponent(`k-${type}-field`);
    },
    /**
     * Check if a field with the given name exists in the fieldset
     * @public
     * @param {string} name field name
     */
    hasField(name) {
      return this.$refs[name]?.[0];
    },
    meetsCondition(field) {
      if (!field.when) {
        return true;
      }

      let result = true;

      Object.keys(field.when).forEach((key) => {
        const value = this.value[key.toLowerCase()];
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

<style>
.k-fieldset {
  border: 0;
}
.k-fieldset .k-grid {
  grid-row-gap: 2.25rem;
}

@media screen and (min-width: 30em) {
  .k-fieldset .k-grid {
    grid-column-gap: 1.5rem;
  }
}

/* Switch off the grid in narrow sections */
.k-sections > .k-column[data-width="1/3"] .k-fieldset .k-grid,
.k-sections > .k-column[data-width="1/4"] .k-fieldset .k-grid {
  grid-template-columns: repeat(1, 1fr);
}

.k-sections > .k-column[data-width="1/3"] .k-fieldset .k-grid .k-column,
.k-sections > .k-column[data-width="1/4"] .k-fieldset .k-grid .k-column {
  grid-column-start: initial;
}
</style>
