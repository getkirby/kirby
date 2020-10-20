<template>
  <header class="k-block-header" :data-is-open="isOpen" @click.prevent="$emit('toggle')">
    <div class="k-block-header-title">
      <k-icon :type="icon || 'box'" class="k-block-header-icon" />
      <span class="k-block-header-name">
        {{ name }}
      </span>
      <span v-if="label" class="k-block-header-label">
        {{ label }}
      </span>
    </div>

    <nav
      v-if="isOpen && hasTabs"
      class="k-block-header-tabs"
    >
      <k-button
        v-for="tab in tabs"
        :key="tab.name"
        :current="currentTab == tab.name"
        class="k-block-header-tab"
        @click.stop="$emit('open', tab.name)"
      >
        {{ tab.label }}
      </k-button>
    </nav>

    <k-button
      v-if="isHidden"
      class="k-block-header-status"
      icon="hidden"
      @click.stop="$emit('show')"
    />
  </header>
</template>

<script>
export default {
  props: {
    icon: String,
    isHidden: Boolean,
    isOpen: Boolean,
    label: [String, Boolean],
    name: String,
    tab: String,
    tabs: Array,
  },
  computed: {
    currentTab() {
      return this.tab || this.tabs[0].name;
    },
    hasTabs() {
      return this.tabs.length > 1
    }
  }
};
</script>

<style lang="scss">
$block-header-padding: 1.5rem;

.k-block-header {
  height: 36px;
  display: flex;
  align-items: center;
  font-size: $text-xs;
  line-height: 1;
  justify-content: space-between;
  cursor: pointer;
}
.k-block-header-icon {
  width: 2rem;
  color: $color-gray-500;
}
.k-block-header-title {
  display: flex;
  align-items: center;
  min-width: 0;
  padding-right: .75rem;
}
.k-block-header-name {
  margin-right: .5rem;
}
.k-block-header-label {
  color: $color-gray-500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-block-header-tabs {
  display: flex;
  align-items: center;
  line-height: 1;
  margin-right: $block-header-padding - .75rem;
}
.k-block-header-tab.k-button {
  height: 36px;
  padding: 0 .75rem;
  display: flex;
  align-items: center;
  font-size: $text-xs;
}
.k-block-header-tab[aria-current]::after {
  position: absolute;
  bottom: -1px;
  left: .75rem;
  right: .75rem;
  content: "";
  background: $color-black;
  height: 2px;
}
.k-block-header-status {
  width: 2.5rem;
  color: $color-gray-500;
}
</style>
