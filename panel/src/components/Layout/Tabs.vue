<template>
  <div
    v-if="tabs && tabs.length > 1"
    :data-theme="theme"
    class="k-tabs"
  >
    <nav>
      <k-button
        v-for="tabButton in visibleTabs"
        :key="tabButton.name"
        :link="'#' + tabButton.name"
        :current="current === tabButton.name"
        :icon="tabButton.icon"
        :tooltip="tabButton.label"
        class="k-tab-button"
      >
        {{ tabButton.label || tabButton.text || tabButton.name }}

        <span
          v-if="tabButton.badge"
          class="k-tabs-badge"
        >
          {{ tabButton.badge }}
        </span>
      </k-button>

      <k-button
        v-if="invisibleTabs.length"
        :text="$t('more')"
        class="k-tab-button k-tabs-dropdown-button"
        icon="dots"
        @click.stop="$refs.more.toggle()"
      />
    </nav>

    <k-dropdown-content
      v-if="invisibleTabs.length"
      ref="more"
      align="right"
      class="k-tabs-dropdown"
    >
      <k-dropdown-item
        v-for="tabButton in invisibleTabs"
        :key="'more-' + tabButton.name"
        :link="'#' + tabButton.name"
        :current="tab === tabButton.name"
        :icon="tabButton.icon"
        :tooltip="tabButton.label"
      >
        {{ tabButton.label || tabButton.name }}
      </k-dropdown-item>
    </k-dropdown-content>
  </div>
</template>

<script>
export default {
  props: {
    tab: String,
    tabs: Array,
    theme: String
  },
  data() {
    return {
      size: null,
      visibleTabs: this.tabs,
      invisibleTabs: []
    }
  },
  computed: {
    current() {
      const tab = this.tabs.find(tab => tab.name === this.tab) || this.tabs[0] || {};
      return tab.name;
    }
  },
  watch: {
    tabs(tabs) {
      this.visibleTabs = tabs,
      this.invisibleTabs = [];
      this.resize(true);
    }
  },
  created() {
    window.addEventListener("resize", this.resize);
  },
  destroyed() {
    window.removeEventListener("resize", this.resize);
  },
  methods: {
    resize(force) {

      if (!this.tabs || this.tabs.length <= 1) {
        return;
      }

      if (this.tabs.length <= 3) {
        this.visibleTabs = this.tabs;
        this.invisibleTabs = [];
        return;
      }

      if (window.innerWidth >= 700) {
        if (this.size === "large" && !force) {
          return;
        }

        this.visibleTabs = this.tabs;
        this.invisibleTabs = [];
        this.size = "large";
      } else {
        if (this.size === "small" && !force) {
          return;
        }

        this.visibleTabs = this.tabs.slice(0, 2);
        this.invisibleTabs = this.tabs.slice(2);
        this.size = "small";
      }

    }
  }
};
</script>

<style>
.k-tabs {
  position: relative;
  background: #e9e9e9;
  border-top: 1px solid var(--color-border);
  border-left: 1px solid var(--color-border);
  border-right: 1px solid var(--color-border);
}
.k-tabs nav {
  display: flex;
  justify-content: center;
  margin-left: -1px;
  margin-right: -1px;
}
.k-tab-button.k-button {
  position: relative;
  z-index: 1;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: .625rem .75rem;
  font-size: var(--text-xs);
  text-transform: uppercase;
  text-align: center;
  font-weight: 500;
  border-left: 1px solid transparent;
  border-right: 1px solid var(--color-border);
  flex-grow: 1;
  flex-shrink: 1;
  flex-direction: column;
  max-width: 15rem;

  @media screen and (min-width: 30em) {
    flex-direction: row;
  }
}
@media screen and (min-width: 30em) {
  .k-tab-button.k-button .k-icon {
    margin-right: .5rem;
  }
}
.k-tab-button.k-button > .k-button-text {
  padding-top: .375rem;
  font-size: 10px;
  overflow: hidden;
  max-width: 10rem;
  text-overflow: ellipsis;
}
[dir="ltr"] .k-tab-button.k-button > .k-button-text {
  padding-left: 0;
}

[dir="rtl"] .k-tab-button.k-button > .k-button-text {
  padding-right: 0;
}

@media screen and (min-width: 30em) {
  .k-tab-button.k-button > .k-button-text {
    font-size: var(--text-xs);
    padding-top: 0;
  }
}
.k-tab-button:last-child {
  border-right: 1px solid transparent;
}
.k-tab-button[aria-current] {
  position: relative;
  background: var(--color-background);
  border-right: 1px solid var(--color-border);
  pointer-events: none;
}
.k-tab-button[aria-current]:first-child {
  border-left: 1px solid var(--color-border);
}

.k-tab-button[aria-current]::before,
.k-tab-button[aria-current]::after {
  position: absolute;
  content: "";
}

.k-tab-button[aria-current]::before {
  left: -1px;
  right: -1px;
  height: 2px;
  top: -1px;
  background: var(--color-black);
}

.k-tab-button[aria-current]::after {
  left: 0;
  right: 0;
  height: 1px;
  bottom: -1px;
  background: var(--color-background);
}
.k-tabs-dropdown {
  top: 100%;
  right: 0;
}
[dir="ltr"] .k-tabs-badge {
  padding-left: .25rem;
}

[dir="rtl"] .k-tabs-badge {
  padding-right: .25rem;
}
.k-tabs[data-theme="notice"] .k-tabs-badge {
  color: var(--color-orange-600);
}
</style>
