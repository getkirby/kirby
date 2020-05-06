<template>
  <div class="k-topbar relative text-white bg-black">
    <k-view>
      <div class="k-topbar-wrapper relative flex items-center">
        <!-- Main Menu -->
        <k-dropdown class="k-topbar-menu">
          <k-button
            :tooltip="$t('menu')"
            icon="bars"
            class="k-topbar-button k-topbar-menu-button"
            @click="$refs.menu.toggle()"
          >
            <k-icon type="angle-down" />
          </k-button>
          <k-dropdown-content
            ref="menu"
            :options="menu"
            class="k-topbar-menu"
            theme="light"
          />
        </k-dropdown>

        <!-- Breadcrumb -->
        <k-breadcrumb
          v-if="breadcrumb"
          :links="breadcrumb"
          class="k-topbar-breadcrumb"
        />

        <!-- Options -->
        <div class="k-topbar-options relative flex items-center">
          <slot name="options" />

          <template v-if="loading">
            <k-loader class="k-topbar-loader" />
          </template>
          <template v-else>
            <k-button
              :tooltip="$t('search')"
              class="k-topbar-button"
              icon="search"
              @click="onSearch"
            />
          </template>
        </div>
      </div>
    </k-view>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    breadcrumb: {
      type: [Array, Boolean],
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    menu: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  methods: {
    onSearch(event) {
      this.$emit("search", event);
    }
  }
}
</script>

<style lang="scss">
.k-topbar {
  flex-shrink: 0;
  height: 2.5rem;
  line-height: 1;
}
.k-topbar-wrapper {
  margin-left: -0.75rem;
  margin-right: -0.75rem;
}

.k-topbar-button {
  display: inline-flex;
  align-items: center;
  height: 2.5rem;
  padding: 0 .75rem;
  font-size: $text-sm;
}
.k-topbar-button .k-button-text {
  display: flex;
  opacity: 1;
}

/** Main Menu **/
.k-topbar-menu {
  flex-shrink: 0;
}
.k-topbar-menu ul {
  padding: 0.5rem 0;
}
.k-topbar-menu-button {
  display: flex;
  align-items: center;
}
.k-topbar-menu-button .k-button-text {
  opacity: 1;
}

/** Breadcrumb **/
.k-topbar-breadcrumb {
  flex-shrink: 1;
  min-width: 0;
  margin-right: .5rem;
  flex-grow: 1;
}

/** Options **/
.k-topbar-options {
  margin-left: auto;
}

/** Loader **/
.k-topbar-loader {
  width: 2.5rem;
  height: 2.5rem;
}

</style>
