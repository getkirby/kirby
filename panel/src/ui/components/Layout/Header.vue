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

    <div v-if="tabs && tabs.length > 1" :key="$route.fullPath" class="k-header-tabs">
      <nav>
        <k-button
          v-for="(tab, tabIndex) in visibleTabs"
          :key="tabIndex"
          :link="'#' + tab.name"
          :current="currentTab && currentTab.name === tab.name"
          :theme="tab.theme"
          :icon="tab.icon"
          :tooltip="tab.label"
          class="k-tab-button"
        >
          {{ tab.label }}
        </k-button>

        <k-button
          v-if="invisibleTabs.length"
          class="k-tab-button k-tabs-dropdown-button"
          icon="dots"
          @click.stop="$refs.more.toggle()"
        >
          {{ $t('more') }}
        </k-button>
      </nav>

      <k-dropdown-content
        v-if="invisibleTabs.length"
        ref="more"
        align="right"
        class="k-tabs-dropdown"
      >
        <k-dropdown-item
          v-for="(tab, tabIndex) in invisibleTabs"
          :link="'#' + tab.name"
          :key="'more-' + tabIndex"
          :current="currentTab && currentTab.name === tab.name"
          :icon="tab.icon"
          :tooltip="tab.label"
        >
          {{ tab.label }}
        </k-dropdown-item>
      </k-dropdown-content>

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
      size: null,
      currentTab: this.tab,
      visibleTabs: this.tabs,
      invisibleTabs: []
    }
  },
  watch: {
    tab() {
      this.currentTab = this.tab;
    },
    tabs(tabs) {
      this.tabs = tabs;
      this.resize();
    }
  },
  created() {
    window.addEventListener("resize", this.resize);
  },
  destroyed() {
    window.removeEventListener("resize", this.resize);
  },
  methods: {
    resize() {

      if (!this.tabs || this.tabs.length <= 1) {
        return;
      }

      if (window.innerWidth >= 700) {
        this.visibleTabs = this.tabs;
        this.invisibleTabs = [];
        this.size = "large";
      } else {
        this.visibleTabs = this.tabs.slice(0, 2);
        this.invisibleTabs = this.tabs.slice(2);
        this.size = "small";
      }
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
.k-tab-button > .k-button-text {
  padding-top: .375rem;
  padding-left: 0 !important; // there's another rule that has a higher specificity and breaks alignment otherwise
  font-size: 10px;
  overflow: hidden;
  text-overflow: ellipsis;

  @media screen and (min-width: $breakpoint-small) {
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

.k-tab-button[data-theme="changes"]:not([aria-current]),
.k-tab-button[data-theme="errors"]:not([aria-current]) {
  position: relative;

  &::after {
    position: absolute;
    content: "";
    left: -1px;
    right: -1px;
    height: 3px;
    top: -1px;
    opacity: .5;
  }
}
.k-tab-button[data-theme="errors"]:not([aria-current]) {
  &::after {
    background: $color-negative;
  }
}
.k-tab-button[data-theme="changes"]:not([aria-current]) {
  &::after {
    background: $color-focus;
  }
}

.k-tabs-dropdown {
  top: 100%;
  right: 0;
}

</style>
