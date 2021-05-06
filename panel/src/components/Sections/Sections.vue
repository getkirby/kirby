<template>
  <k-box
    v-if="tabs.length === 0"
    :text="empty"
    theme="info"
  />
  <k-grid v-else class="k-sections" gutter="large">
    <k-column
      v-for="(column, columnIndex) in currentTab.columns"
      :key="parent + '-column-' + columnIndex"
      :width="column.width"
      :sticky="column.sticky"
    >
      <template v-for="(section, sectionIndex) in column.sections">
        <template v-if="meetsCondition(section)">
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
            <k-box :key="parent + '-column-' + columnIndex + '-section-' + sectionIndex" :text="$t('error.section.type.invalid', { type: section.type })" theme="negative" />
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
    parent: String,
    tab: String,
    tabs: Array,
  },
  computed: {
    currentTab() {
      return this.tabs.find(tab => tab.name === this.tab) || this.tabs[0] || {};
    },
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
  padding-bottom: .75rem;
  min-height: 2rem;
}
.k-section-header .k-button-group {
  position: absolute;
  top: -.875rem;
}
[dir="ltr"] .k-section-header .k-button-group {
  right: 0;
}

[dir="rtl"] .k-section-header .k-button-group {
  left: 0;
}
</style>
