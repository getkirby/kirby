<template>
  <k-grid
    class="k-sections"
    gutter="large"
  >
    <k-column
      v-for="(column, columnIndex) in columns"
      :key="columnIndex"
      :width="column.width"
      :sticky="column.sticky"
    >
      <template v-if="column.sections && Object.keys(column.sections).length">
        <template v-for="(section, sectionName) in column.sections">
          <k-error-boundary :key="sectionName">
            <component
              :is="'k-' + section.type + '-section'"
              v-if="sectionExists(section.type)"
              v-bind="section"
              :label="label(section, sectionName)"
              :lock="lock"
              :value="model"
              v-on="listeners"
            />
          </k-error-boundary>
        </template>
      </template>
      <template v-else>
        <k-empty layout="cards" icon="dashboard">Column: {{ column.width }}</k-empty>
      </template>
    </k-column>
  </k-grid>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    columns: Array,
    lock: {
      type: [Boolean, Object],
      default: false,
    },
    value: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  computed: {
    listeners() {
      return {
        ...this.$listeners,
        input: this.onInput
      }
    }
  },
  data() {
    return {
      model: this.value
    }
  },
  watch: {
    value() {
      this.model = this.value
    }
  },
  methods: {
    sectionExists(type) {
      if (this.$helper.isComponent(type + "-section")) {
        return true;
      }

      throw new Error(this.$t('error.section.type.invalid', { type: section.type }));
    },
    label(section, name) {
      return section.label || section.headline || this.$helper.string.ucfirst(name);
    },
    onInput(value) {
      this.model = {
        ...this.model,
        ...value
      };

      this.$emit("input", this.model);
    }
  }
};
</script>

<style lang="scss">
.k-sections .k-section {
  margin-bottom: 3rem;
}
</style>
