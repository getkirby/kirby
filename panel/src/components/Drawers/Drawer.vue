<template>
  <k-overlay
    ref="overlay"
    :dimmed="false"
    @close="onClose"
    @open="onOpen"
  >
    <div
      :data-nested="nested"
      class="k-drawer"
      @mousedown="click = true"
      @mouseup="mouseup"
    >
      <div class="k-drawer-box" @mousedown.stop="click = false">
        <header class="k-drawer-header">
          <h2 v-if="breadcrumb.length === 1" class="k-drawer-title">
            <k-icon :type="icon" /> {{ title }}
          </h2>
          <ul v-else class="k-drawer-breadcrumb">
            <li v-for="crumb in breadcrumb" :key="crumb.id">
              <k-button :icon="crumb.icon" @click="goTo(crumb.id)">
                {{ crumb.title }}
              </k-button>
            </li>
          </ul>
          <nav
            v-if="hasTabs"
            class="k-drawer-tabs"
          >
            <k-button
              v-for="tabButton in tabs"
              :key="tabButton.name"
              :current="tab == tabButton.name"
              class="k-drawer-tab"
              @click.stop="$emit('tab', tabButton.name)"
            >
              {{ tabButton.label }}
            </k-button>
          </nav>
          <nav class="k-drawer-options">
            <slot name="options" />
            <k-button
              class="k-drawer-option"
              icon="check"
              @click="close"
            />
          </nav>
        </header>
        <div class="k-drawer-body">
          <slot />
        </div>
      </div>
    </div>
  </k-overlay>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    icon: String,
    tab: String,
    tabs: Object,
    title: String
  },
  data() {
    return {
      click: false
    };
  },
  computed: {
    breadcrumb() {
      return this.$store.state.drawers.open;
    },
    hasTabs() {
      return this.tabs && Object.keys(this.tabs).length > 1;
    },
    index() {
      return this.breadcrumb.findIndex(item => item.id === this._uid);
    },
    nested() {
      return this.index > 0;
    }
  },
  watch: {
    index() {
      if (this.index === -1) {
        this.close();
      }
    }
  },
  destroyed() {
    this.$store.dispatch("drawers/close", this._uid);
  },
  methods: {
    close() {
      this.$refs.overlay.close();
    },
    goTo(id) {
      if (id === this._uid) {
        return true;
      }
      this.$store.dispatch("drawers/goto", id);
    },
    mouseup() {
      if (this.click === true) {
        this.close();
      }

      this.click = false;
    },
    onClose() {
      this.$store.dispatch("drawers/close", this._uid);
      this.$emit("close");
    },
    onOpen() {
      this.$store.dispatch("drawers/open", {
        id: this._uid,
        icon: this.icon,
        title: this.title
      });
      this.$emit("open");
    },
    open() {
      this.$refs.overlay.open();
    }
  }
}
</script>

<style>
.k-drawer {
  --drawer-header-height: 2.5rem;
  --drawer-header-padding: 1.5rem;

  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: var(--z-toolbar);
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  background: rgba(0, 0, 0, .2);
}
.k-drawer-box {
  position: relative;
  flex-basis: 50rem;
  display: flex;
  flex-direction: column;
  background: var(--color-background);
  box-shadow: var(--shadow-xl);
}
.k-drawer-header {
  flex-shrink: 0;
  height: var(--drawer-header-height);
  padding-left: var(--drawer-header-padding);
  display: flex;
  align-items: center;
  line-height: 1;
  justify-content: space-between;
  background: var(--color-white);
  font-size: var(--text-sm);
}
.k-drawer-title {
  padding: 0 .75rem;
}
.k-drawer-title,
.k-drawer-breadcrumb {
  display: flex;
  flex-grow: 1;
  align-items: center;
  min-width: 0;
  margin-left: -.75rem;
  font-size: var(--text-sm);
  font-weight: var(--font-normal);
}
.k-drawer-breadcrumb li:not(:last-child) .k-button::after {
  position: absolute;
  display: inline-flex;
  right: -.75rem;
  width: 1.5rem;
  justify-content: center;
  align-items: center;
  content: "›";
  color: var(--color-gray-500);
  height: var(--drawer-header-height);
}
.k-drawer-title .k-icon,
.k-drawer-breadcrumb .k-icon {
  width: 1rem;
  color: var(--color-gray-500);
  margin-right: .5rem;
}
.k-drawer-breadcrumb .k-button {
  display: inline-flex;
  align-items: center;
  height: var(--drawer-header-height);
  padding: 0 .75rem;
}
.k-drawer-breadcrumb .k-button-text {
  opacity: 1;
}
.k-drawer-breadcrumb .k-button .k-button-icon ~ .k-button-text {
  padding-left: 0;
}
.k-drawer-tabs {
  display: flex;
  align-items: center;
  line-height: 1;
  margin-right: .75rem;
}
.k-drawer-tab.k-button {
  height: var(--drawer-header-height);
  padding: 0 .75rem;
  display: flex;
  align-items: center;
  font-size: var(--text-xs);
}
.k-drawer-tab.k-button[aria-current]::after {
  position: absolute;
  bottom: -1px;
  left: .75rem;
  right: .75rem;
  content: "";
  background: var(--color-black);
  height: 2px;
}

.k-drawer-options {
  padding-right: .75rem;
}
.k-drawer-option.k-button {
  width: var(--drawer-header-height);
  height: var(--drawer-header-height);
  color: var(--color-gray-500);
  line-height: 1;
}
.k-drawer-option.k-button:focus,
.k-drawer-option.k-button:hover {
  color: var(--color-black);
}

.k-drawer-body {
  padding: 1.5rem;
  flex-grow: 1;
  overflow-y: auto;
  background: var(--color-background);
}

/* Nested drawers */
.k-drawer[data-nested] {
  background: none;
}
</style>
