<template>
  <kirby-dropdown class="kirby-tabs-dropdown">
    <kirby-button
      :key="tab.name + '-toggle'"
      :icon="tab.icon"
      class="kirby-tabs-dropdown-toggle"
      @click="$refs.tabs.toggle()"
    >{{ tab.label }}</kirby-button>
    <kirby-dropdown-content ref="tabs">
      <kirby-dropdown-item
        v-for="tab in tabs"
        :key="tab.name + '-dropdown-item'"
        :icon="tab.icon"
        :link="'#' + tab.name"
      >{{ tab.label }}</kirby-dropdown-item>
    </kirby-dropdown-content>
  </kirby-dropdown>
</template>

<script>
export default {
  props: {
    tabs: Array
  },
  data() {
    return {
      tab: this.tabs[0]
    };
  },
  methods: {
    open(tabName) {
      this.tabs.forEach(tab => {
        if (tab.name === tabName) {
          this.tab = tab;
          this.$emit("open", tab.name);
        }
      });
    }
  }
};
</script>

<style lang="scss">
.kirby-tabs-dropdown-toggle {
  position: relative;
}
.kirby-tabs-dropdown-toggle::after {
  position: absolute;
  content: "";
  left: 0.75rem;
  right: 0.75rem;
  bottom: -2px;
  height: 2px;
  background: $color-dark;
}
.kirby-tabs-dropdown-toggle::before {
  position: absolute;
  content: "";
  border-bottom: 4px solid $color-dark;
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  bottom: 0;
  left: 50%;
  margin-left: -4px;
}
</style>
