<template>
  <k-grid
    class="k-sections"
    gutter="large"
  >
    <k-column
      v-for="(column, columnIndex) in columns"
      :key="parent + '-column-' + columnIndex"
      :width="column.width"
      :sticky="column.sticky"
    >
      <template v-for="(section, sectionIndex) in visibleSections(column.sections)">
        <component
          :is="'k-' + section.type + '-section'"
          v-if="exists(section.type)"
          :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex + '-' + blueprint"
          :name="section.name"
          :parent="parent"
          :blueprint="blueprint"
          :column="column.width"
          :class="'k-section k-section-name-' + section.name"
          v-bind="section"
          @submit="$emit('submit', $event)"
        />
        <template v-else>
          <k-box
            :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex"
            :text="$t('error.section.type.invalid', { type: section.type })"
            theme="negative"
          />
        </template>
      </template>
    </k-column>
  </k-grid>
</template>

<script>
import Vue from "vue";

export default {
  props: {
    parent: String,
    blueprint: String,
    columns: [Array, Object]
  },
  computed: {
    content() {
      return this.$store.getters["content/values"]();
    }
  },
  methods: {
    exists(type) {
      return Vue.options.components["k-" + type + "-section"];
    },
    meetsCondition(section) {

      if (!section.when) {
        return true;
      }

      let result = true;

      Object.keys(section.when).forEach(key => {
        const value     = this.content[key.toLowerCase()];
        const condition = section.when[key];

        if (value !== condition) {
          result = false;
        }
      });

      return result;

    },
    visibleSections(sections) {
      return Object.values(sections).filter(section => this.meetsCondition(section));
    }
  }
};
</script>
