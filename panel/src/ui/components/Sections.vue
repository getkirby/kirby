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
      <template v-for="(section, sectionName) in column.sections">
        <k-error-boundary :key="sectionName">
          <component
            :is="'k-' + section.type + '-section'"
            v-if="sectionExists(section.type)"
            v-bind="section"
            :label="label(section, sectionName)"
            v-on="$listeners"
          />
        </k-error-boundary>
      </template>
    </k-column>
  </k-grid>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    columns: Array
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
    }
  }
};
</script>

<style lang="scss">
.k-sections .k-section {
  margin-bottom: 3rem;
}
</style>
