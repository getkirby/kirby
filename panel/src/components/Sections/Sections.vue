<template>
  <kirby-grid class="kirby-sections" gutter="fluid">
    <kirby-column v-for="(column, columnIndex) in columns" :key="parent + '-column-' + columnIndex" :width="column.width">
      <template v-for="(section, sectionIndex) in column.sections">
        <component
          v-if="exists(section.type)"
          :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex"
          :is="'kirby-' + section.type + '-section'"
          :name="section.name"
          :parent="parent"
          :blueprint="blueprint"
          class="kirby-section"
          @submit="$emit('submit', $event)"
        />
        <template v-else>
          <kirby-box :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex" :text="$t('error.blueprint.section.type.invalid', { type: section.type })" />
        </template>
      </template>
    </kirby-column>
  </kirby-grid>
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
      return Vue.options.components["kirby-" + type + "-section"];
    }
  }
};
</script>

<style lang="scss">
.kirby-sections {
  padding-bottom: 3rem;
}
.kirby-section {
  padding-bottom: 3rem;
}
.kirby-section-header {
  position: relative;
  display: flex;
  align-items: center;
  z-index: 1;
}
.kirby-section-header .kirby-headline {
  line-height: 1.25rem;
  padding-bottom: 0.75rem;
  flex-grow: 1;
}
.kirby-section-header .kirby-button-group {
  position: absolute;
  top: -1rem;
  right: 0;
}
</style>
