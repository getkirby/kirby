<template>
  <k-overlay
    ref="overlay"
    :dimmed="false"
    @close="$emit('close')"
    @open="$emit('open')"
  >
    <div class="k-drawer" @mousedown="click = true" @mouseup="mouseup">
      <div class="k-drawer-box" @mousedown.stop="click = false">
        <header class="k-drawer-header">
          <k-icon :type="icon" class="k-drawer-icon" />
          <slot name="title">
            <h2 class="k-drawer-title">
              {{ title }}
            </h2>
          </slot>
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
              icon="cancel"
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
    hasTabs() {
      return this.tabs && Object.keys(this.tabs).length > 1;
    }
  },
  methods: {
    close() {
      this.$refs.overlay.close();
    },
    mouseup() {
      if (this.click === true) {
        this.close();
      }

      this.click = false;
    },
    open() {
      this.$refs.overlay.open();
    }
  }
}
</script>

<style lang="scss">
$drawer-header-height: 2.5rem;
$drawer-header-padding: 1.5rem;

.k-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: z-index(toolbar);
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  background: rgba(#000, .2);
}
.k-drawer-box {
  position: relative;
  flex-basis: 50rem;
  display: flex;
  flex-direction: column;
  background: $color-background;
  box-shadow: $shadow-xl;
}
.k-drawer-header {
  flex-shrink: 0;
  height: $drawer-header-height;
  padding-left: $drawer-header-padding;
  display: flex;
  align-items: center;
  font-size: $text-xs;
  line-height: 1;
  justify-content: space-between;
  background: $color-white;
}
.k-drawer-icon {
  width: 1rem;
  margin-right: .5rem;
  color: $color-gray-500;
}
.k-drawer-title {
  display: flex;
  flex-grow: 1;
  align-items: center;
  min-width: 0;
  padding-right: .75rem;
  font-size: $text-sm;
  font-weight: $font-normal;
  line-height: 1;
}
.k-drawer-tabs {
  display: flex;
  align-items: center;
  line-height: 1;
  margin-right: .75rem;
}
.k-drawer-tab.k-button {
  height: $drawer-header-height;
  padding: 0 .75rem;
  display: flex;
  align-items: center;
  font-size: $text-xs;
}
.k-drawer-tab.k-button[aria-current]::after {
  position: absolute;
  bottom: -1px;
  left: .75rem;
  right: .75rem;
  content: "";
  background: $color-black;
  height: 2px;
}

.k-drawer-options {
  padding-right: .75rem;
}
.k-drawer-option.k-button {
  width: $drawer-header-height;
  height: $drawer-header-height;
  color: $color-gray-500;
  line-height: 1;
}
.k-drawer-option.k-button:focus,
.k-drawer-option.k-button:hover {
  color: $color-black;
}

.k-drawer-body {
  padding: 1.5rem;
  flex-grow: 1;
  overflow-y: auto;
  background: $color-background;
}
</style>
