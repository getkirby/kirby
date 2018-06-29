<template>
  <header class="kirby-header">
    <kirby-headline tag="h1" size="huge">
      <span v-if="$listeners.edit" class="kirby-headline-editable" @click="$emit('edit')">
        <slot />
        <kirby-icon type="edit" />
      </span>
      <slot v-else />
    </kirby-headline>
    <kirby-bar>
      <slot slot="left" name="left" class="kirby-header-left" />
      <slot slot="right" name="right" class="kirby-header-right" />
    </kirby-bar>

    <div v-if="tabs && tabs.length > 1" :data-compact="tabs.length >= 5" class="kirby-header-tabs">
      <nav>
        <kirby-button
          v-for="(tab, tabIndex) in tabs"
          :key="tabIndex"
          :link="'#' + tab.name"
          :current="currentTab && currentTab.name === tab.name"
          :icon="tab.icon"
          :tooltip="tab.label"
          class="kirby-tab-button"
        >
          {{ tab.label }}
        </kirby-button>
      </nav>
    </div>
  </header>
</template>

<script>
export default {
  props: {
    tabs: Array,
    tab: Object
  },
  data() {
    return {
      currentTab: this.tab
    }
  },
  watch: {
    tab() {
      this.currentTab = this.tab;
    }
  }
}
</script>

<style lang="scss">
.kirby-header {
  border-bottom: 1px solid $color-border;
  margin-bottom: 2rem;
  padding-top: 4vh;
}
.kirby-header .kirby-headline {
  min-height: 1.25em;
}
.kirby-header .kirby-headline-editable {
  cursor: pointer;
  display: flex;
  align-items: baseline;
}
.kirby-header .kirby-headline-editable .kirby-icon {
  color: $color-light-grey;
  margin-left: .5rem;
  opacity: 0;
  transition: opacity .3s;
}
.kirby-header .kirby-headline-editable:hover .kirby-icon {
  opacity: 1;
}

.kirby-header-tabs {
  position: relative;
  background: #e9e9e9;
  border-top: 1px solid $color-border;
  border-left: 1px solid $color-border;
  border-right: 1px solid $color-border;
}
.kirby-header-tabs nav {
  display: flex;
  justify-content: center;
  margin-left: -1px;
  margin-right: -1px;
}
.kirby-header-tabs[data-compact] .kirby-tab-button:not([aria-current]) .kirby-button-text {
  display: none;
}

.kirby-tab-button {
  position: relative;
  z-index: 1;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: .625rem 0;
  font-size: $font-size-tiny;
  text-transform: uppercase;
  font-weight: 500;
  border-left: 1px solid transparent;
  border-right: 1px solid $color-border;
  flex-grow: 1;
  flex-direction: column;

  @media screen and (min-width: $breakpoint-small) {
    flex-direction: row;
  }
  @media screen and (min-width: $breakpoint-medium) {
    max-width: 13rem;
  }
}
.kirby-tab-button .kirby-button-text {
  padding-top: .375rem;
  padding-left: 0 !important; // there's another rule that has a higher specificity and breaks alignment otherwise
  font-size: 10px;
  overflow: hidden;
  max-width: 2.5rem;
  text-overflow: ellipsis;

  @media screen and (min-width: $breakpoint-small) {
    max-width: none;
    font-size: $font-size-tiny;
    padding-top: 0;
    padding-left: .5rem !important; // same as above
  }

}
.kirby-tab-button:last-child {
  border-right: 1px solid transparent;
}
.kirby-tab-button[aria-current] {
  position: relative;
  background: $color-background;
  border-right: 1px solid $color-border;

  &:first-child {
    border-left: 1px solid $color-border;
  }

  &::before,
  &::after {
    position: absolute;
    content: "";
  }

  &::before {
    left: -1px;
    right: -1px;
    height: 2px;
    top: -1px;
    background: $color-dark;
  }

  &::after {
    left: 0;
    right: 0;
    height: 1px;
    bottom: -1px;
    background: $color-background;
  }

}
</style>
