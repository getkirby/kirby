<template>
  <header :data-editable="editable" class="k-header">
    <k-headline tag="h1" size="huge">
      <span v-if="editable && $listeners.edit" class="k-headline-editable" @click="$emit('edit')">
        <slot />
        <k-icon type="edit" />
      </span>
      <slot v-else />
    </k-headline>
    <k-bar>
      <slot slot="left" name="left" class="k-header-left" />
      <slot slot="right" name="right" class="k-header-right" />
    </k-bar>

    <div v-if="tabs && tabs.length > 1" :data-compact="tabs.length >= 5" class="k-header-tabs">
      <nav>
        <k-button
          v-for="(tab, tabIndex) in tabs"
          :key="tabIndex"
          :link="'#' + tab.name"
          :current="currentTab && currentTab.name === tab.name"
          :icon="tab.icon"
          :tooltip="tab.label"
          class="k-tab-button"
        >
          {{ tab.label }}
        </k-button>
      </nav>
    </div>
  </header>
</template>

<script>
export default {
  props: {
    editable: Boolean,
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
.k-header {
  border-bottom: 1px solid $color-border;
  margin-bottom: 2rem;
  padding-top: 4vh;
}
.k-header .k-headline {
  min-height: 1.25em;
}
.k-header .k-headline-editable {
  cursor: pointer;
}
.k-header .k-headline-editable .k-icon {
  color: $color-light-grey;
  margin-left: .5rem;
  opacity: 0;
  transition: opacity .3s;
  display: inline-block;
}
.k-header .k-headline-editable:hover .k-icon {
  opacity: 1;
}

.k-header-tabs {
  position: relative;
  background: #e9e9e9;
  border-top: 1px solid $color-border;
  border-left: 1px solid $color-border;
  border-right: 1px solid $color-border;
}
.k-header-tabs nav {
  display: flex;
  justify-content: center;
  margin-left: -1px;
  margin-right: -1px;
}
.k-header-tabs[data-compact] .k-tab-button:not([aria-current]) .k-button-text {
  display: none;
}

.k-tab-button {
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
.k-tab-button .k-button-text {
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
.k-tab-button:last-child {
  border-right: 1px solid transparent;
}
.k-tab-button[aria-current] {
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
