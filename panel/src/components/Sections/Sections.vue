<template>
  <k-grid class="k-sections" gutter="large">
    <k-column v-for="(column, columnIndex) in columns" :key="parent + '-column-' + columnIndex" :width="column.width">
      <template v-for="(section, sectionIndex) in column.sections" v-if="meetsCondition(section)">
        <component
          v-if="exists(section.type)"
          :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex + '-' + blueprint"
          :is="'k-' + section.type + '-section'"
          :name="section.name"
          :parent="parent"
          :blueprint="blueprint"
          :column="column.width"
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
  align-items: baseline;
  z-index: 1;
}
.k-section-header .k-headline {
  line-height: 1.25rem;
  padding-bottom: 0.75rem;
  min-height: 2rem;
}
.k-section-header .k-button-group {
  position: absolute;
  top: -.875rem;

  [dir="ltr"] & {
    right: 0;
  }

  [dir="rtl"] & {
    left: 0;
  }
}

</style>
