<template>
  <k-dropdown class="k-tabs-dropdown">
    <k-button
      :key="tab.name + '-toggle'"
      :icon="tab.icon"
      class="k-tabs-dropdown-toggle"
      @click="$refs.tabs.toggle()"
    >{{ tab.label }}</k-button>
    <k-dropdown-content ref="tabs">
      <k-dropdown-item
        v-for="tab in tabs"
        :key="tab.name + '-dropdown-item'"
        :icon="tab.icon"
        :link="'#' + tab.name"
      >{{ tab.label }}</k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
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
.k-tabs-dropdown-toggle {
  position: relative;
}
.k-tabs-dropdown-toggle::after {
  position: absolute;
  content: "";
  left: 0.75rem;
  right: 0.75rem;
  bottom: -2px;
  height: 2px;
  background: $color-dark;
}
.k-tabs-dropdown-toggle::before {
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
