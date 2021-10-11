<template>
  <k-box
    v-if="tab.columns.length === 0"
    :html="true"
    :text="empty"
    theme="info"
  />
  <k-grid v-else class="k-sections" gutter="large">
    <k-column
      v-for="(column, columnIndex) in tab.columns"
      :key="parent + '-column-' + columnIndex"
      :width="column.width"
      :sticky="column.sticky"
    >
      <template v-for="(section, sectionIndex) in column.sections">
        <template v-if="meetsCondition(section)">
          <component
            :is="'k-' + section.type + '-section'"
            v-if="exists(section.type)"
            :key="
              parent +
              '-column-' +
              columnIndex +
              '-section-' +
              sectionIndex +
              '-' +
              blueprint
            "
            :column="column.width"
            :lock="lock"
            :name="section.name"
            :parent="parent"
            :timestamp="$view.timestamp"
            :class="'k-section k-section-name-' + section.name"
            v-bind="section"
            @submit="$emit('submit', $event)"
          />
          <template v-else>
            <k-box
              :key="
                parent + '-column-' + columnIndex + '-section-' + sectionIndex
              "
              :text="$t('error.section.type.invalid', { type: section.type })"
              theme="negative"
            />
          </template>
        </template>
      </template>
    </k-column>
  </k-grid>
</template>

<script>
export default {
  props: {
    empty: String,
    blueprint: String,
    lock: [Boolean, Object],
    parent: String,
    tab: Object
  },
  computed: {
    content() {
      return this.$store.getters["content/values"]();
    }
  },
  methods: {
    exists(type) {
      return this.$helper.isComponent(`k-${type}-section`);
    },
    meetsCondition(section) {
      if (!section.when) {
        return true;
      }

      let result = true;

      Object.keys(section.when).forEach((key) => {
        const value = this.content[key.toLowerCase()];
        const condition = section.when[key];

        if (value !== condition) {
          result = false;
        }
      });

      return result;
    }
  }
};
</script>

<style>
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
  top: -0.875rem;
  inset-inline-end: 0;
}
</style>
