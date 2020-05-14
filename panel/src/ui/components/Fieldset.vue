<template>
  <fieldset class="k-fieldset">
    <k-grid>
      <k-column
        v-for="(field, fieldName) in visibleFields"
        :key="field.signature"
        :width="field.width"
      >
        <k-error-boundary>
          <component
            :is="'k-' + field.type + '-field'"
            v-if="hasFieldType(field.type)"
            :disabled="disabled || field.disabled"
            :label="label(fieldName)"
            :name="fieldName"
            :novalidate="novalidate"
            :ref="fieldName"
            v-model="values[fieldName]"
            v-bind="field"
            @input="onInput"
            @focus="$emit('focus', $event, field, fieldName)"
            @submit="$emit('submit', $event, field, fieldName)"
          />
          <k-box
            v-else
            theme="negative"
          >
            <k-text size="small">
              The field type <strong>"{{ field.type }}"</strong> does not exist
            </k-text>
          </k-box>
        </k-error-boundary>
      </k-column>
    </k-grid>
  </fieldset>
</template>

<script>
import Vue from "vue";

export default {
  props: {
    autofocus: {
      type: Boolean,
      default: false,
    },
    disabled: Boolean,
    fields: {
      type: Object,
      default() {
        return {};
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
  mounted() {
    if (this.autofocus) {
      this.focus();
    }
  },
  data() {
    return {
      values: this.value
    };
  },
  computed: {
    visibleFields() {
      let fields = {};

      Object.keys(this.fields).forEach(name => {
        let field = this.fields[name];

        // guess the field type from the name
        if (!field.type) {
          field.type = name;
        }

        // conditional fields
        if (field.type !== 'hidden' && this.meetsCondition(field)) {
          fields[name] = field;
        }
      });

      return fields;
    }
  },
  watch: {
    value() {
      this.values = this.value;
    }
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
      return this.$helper.isComponent(type + "-field");
    },
    hasField(name) {
      return this.$refs[name] && this.$refs[name][0];
    },
    label(name) {
      return this.fields[name].label || this.$helper.string.ucfirst(name);
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
    onInput() {
      this.$emit("input", this.values);
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

@media screen and (min-width: $breakpoint-sm) {
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
