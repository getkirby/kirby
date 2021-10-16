<template>
  <div class="k-topbar">
    <k-view>
      <div class="k-topbar-wrapper">
        <!-- menu -->
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
            theme="light"
            class="k-topbar-menu"
          />
        </k-dropdown>

        <!-- breadcrumb -->
        <k-breadcrumb
          :crumbs="breadcrumb"
          :view="view"
          class="k-topbar-breadcrumb"
        />

        <div class="k-topbar-signals">
          <!-- notifications -->
          <k-button
            v-if="notification"
            :text="notification.message"
            theme="positive"
            class="k-topbar-notification k-topbar-button"
            @click="$store.dispatch('notification/close')"
          />

          <!-- registration -->
          <k-registration v-else-if="!license" />

          <!-- unsaved changes indicator -->
          <k-form-indicator />

          <!-- search -->
          <k-button
            :tooltip="$t('search')"
            class="k-topbar-button"
            icon="search"
            @click="$refs.search.open()"
          />
        </div>
      </div>
    </k-view>

    <!-- search overlay -->
    <k-search ref="search" :type="$view.search || 'pages'" :types="$searches" />
  </div>
</template>

<script>
export default {
  props: {
    breadcrumb: Array,
    license: Boolean,
    menu: Array,
    title: String,
    view: Object
  },
  computed: {
    notification() {
      if (
        this.$store.state.notification.type &&
        this.$store.state.notification.type !== "error"
      ) {
        return this.$store.state.notification;
      } else {
        return null;
      }
    }
  }
};
</script>

<style>
.k-topbar {
  --bg: var(--color-gray-900);

  position: relative;
  color: var(--color-white);
  flex-shrink: 0;
  height: 2.5rem;
  line-height: 1;
  background: var(--bg);
}
.k-topbar-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-inline: -0.75rem;
}
.k-topbar-wrapper::after {
  position: absolute;
  content: "";
  height: 2.5rem;
  background: var(--bg);
  inset-inline-start: 100%;
  width: 3rem;
}

.k-topbar-menu {
  flex-shrink: 0;
}
.k-topbar-menu ul {
  padding: 0.5rem 0;
}
.k-topbar .k-button[data-theme] {
  color: var(--theme-light);
}
.k-topbar .k-button-text {
  opacity: 1;
}

.k-topbar-menu-button {
  display: flex;
  align-items: center;
}
.k-topbar-menu .k-link[aria-current] {
  color: var(--color-focus);
  font-weight: 500;
}
.k-topbar-button {
  padding: 0.75rem;
  line-height: 1;
  font-size: var(--text-sm);
}
.k-topbar-button .k-button-text {
  display: flex;
}
.k-topbar-view-button {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  padding-inline-end: 0;
}
.k-topbar-view-button .k-icon {
  margin-inline-end: 0.5rem;
}

.k-topbar-signals {
  position: absolute;
  top: 0;
  inset-inline-end: 0;
  background: var(--bg);
  height: 2.5rem;
  display: flex;
  align-items: center;
}
.k-topbar-signals::before {
  position: absolute;
  content: "";
  top: -0.5rem;
  bottom: 0;
  width: 0.5rem;
  background: -webkit-linear-gradient(
    inline-start,
    rgba(17, 17, 17, 0),
    rgba(17, 17, 17, 1)
  );
}
.k-topbar-signals .k-button {
  line-height: 1;
}

.k-topbar-notification {
  font-weight: var(--font-bold);
  line-height: 1;
  display: flex;
}

@media screen and (max-width: 30em) {
  .k-topbar .k-button[data-theme="negative"] .k-button-text {
    display: none;
  }
}
</style>
