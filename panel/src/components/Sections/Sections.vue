<template>
  <k-grid class="k-sections" gutter="large">
    <k-column v-for="(column, columnIndex) in columns" :key="parent + '-column-' + columnIndex" :width="column.width">
      <template v-for="(section, sectionIndex) in column.sections">
        <component
          v-if="exists(section.type)"
          :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex + '-' + blueprint"
          :is="'k-' + section.type + '-section'"
          :name="section.name"
          :parent="parent"
          :blueprint="blueprint"
          :class="'k-section k-section-name-' + section.name"
          v-bind="section"
          @submit="$emit('submit', $event)"
        />
        <template v-else>
          <k-box :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex" :text="$t('error.section.type.invalid', { type: section.type })" theme="negative" />
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
    columns: Array
  },
  methods: {
    exists(type) {
      return Vue.options.components["k-" + type + "-section"];
    }
  }
};
</script>

<style lang="scss">
.k-sections {
  padding-bottom: 3rem;
}
.k-section {
  padding-bottom: 3rem;
}
.k-section-header {
  position: relative;
  display: flex;
  align-items: center;
  z-index: 1;
}
.k-section-header .k-headline {
  line-height: 1.25rem;
  padding-bottom: 0.75rem;
  flex-grow: 1;
  min-height: 2rem;
}
.k-section-header .k-button-group {
  position: absolute;
  top: -.975rem;

  [dir="ltr"] & {
    right: 0;
  }

  [dir="rtl"] & {
    left: 0;
  }
}
.k-section-help {
  padding-top: 0.5rem;
}
</style>
