<template>
  <header v-if="isVisible" class="kirby-topbar">
    <kirby-view>
      <div class="kirby-topbar-wrapper">
        <kirby-dropdown class="kirby-topbar-menu">
          <kirby-button icon="bars" class="kirby-topbar-button kirby-topbar-menu-button" @click="$refs.menu.toggle()">
            <kirby-icon type="angle-down" />
          </kirby-button>
          <kirby-dropdown-content ref="menu" class="kirby-topbar-menu">
            <ul>
              <li
                v-for="(view, viewName) in views"
                v-if="view.menu"
                :aria-current="$route.meta.view === viewName"
                :key="'menu-item-' + viewName"
              >
                <kirby-dropdown-item
                  :disabled="access[viewName] === false"
                  :icon="view.icon"
                  :link="view.link"
                >
                  {{ $t(`view.${viewName}`, view.label) }}
                </kirby-dropdown-item>
              </li>
              <li><hr></li>
              <li :aria-current="$route.meta.view === 'account'">
                <kirby-dropdown-item icon="account" link="/account">
                  {{ $t("view.account") }}
                </kirby-dropdown-item>
              </li>
              <li><hr></li>
              <li>
                <kirby-dropdown-item icon="logout" link="/logout">
                  {{ $t("logout") }}
                </kirby-dropdown-item>
              </li>
            </ul>
          </kirby-dropdown-content>
        </kirby-dropdown>

        <kirby-link
          v-tab
          v-if="view"
          :to="view.link"
          class="kirby-topbar-button kirby-topbar-view-button"
        >
          <kirby-icon :type="view.icon" /> {{ $t(`view.${$store.state.view}`, view.label) }}
        </kirby-link>

        <kirby-dropdown v-if="$store.state.breadcrumb.length > 1" class="kirby-topbar-breadcrumb-menu">
          <kirby-button class="kirby-topbar-button" @click="$refs.crumb.toggle()">
            …
            <kirby-icon type="angle-down" />
          </kirby-button>

          <kirby-dropdown-content ref="crumb">
            <kirby-dropdown-item :icon="view.icon" :link="view.link">
              {{ $t(`view.${$store.state.view}`, view.label) }}
            </kirby-dropdown-item>
            <kirby-dropdown-item
              v-for="(crumb, index) in $store.state.breadcrumb"
              :key="'crumb-' + index + '-dropdown'"
              :icon="view.icon"
              :link="crumb.link"
            >
              {{ crumb.label }}
            </kirby-dropdown-item>
          </kirby-dropdown-content>
        </kirby-dropdown>

        <nav class="kirby-topbar-crumbs">
          <kirby-link
            v-tab
            v-for="(crumb, index) in $store.state.breadcrumb"
            :key="'crumb-' + index"
            :to="crumb.link"
          >
            {{ crumb.label }}
          </kirby-link>
        </nav>

        <div class="kirby-topbar-signals">
          <kirby-button
            v-if="notification"
            class="kirby-topbar-notification"
            theme="positive"
            @click="$store.dispatch('notification/close')"
          >
            {{ notification.message }}
          </kirby-button>
          <kirby-button icon="search" @click="$store.dispatch('search', true)" />
        </div>
      </div>
    </kirby-view>
  </header>
</template>

<script>
import views from "@/config/views.js";

export default {
  computed: {
    view() {
      return views[this.$store.state.view];
    },
    views() {
      return views;
    },
    user() {
      return this.$store.state.user.current;
    },
    access() {
      return this.user.permissions.access !== false;
    },
    notification() {
      if (
        this.$store.state.notification.type &&
        this.$store.state.notification.type !== "error"
      ) {
        return this.$store.state.notification;
      } else {
        return null;
      }
    },
    isVisible() {
      return this.user && !this.$route.meta.outside && this.view;
    }
  }
};
</script>

<style lang="scss">
.kirby-topbar {
  position: relative;
  color: $color-white;
  flex-shrink: 0;
  height: 2.5rem;
  background: $color-dark;
}
.kirby-topbar-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: -0.75rem;
  margin-right: -0.75rem;
}
.kirby-topbar-menu {
  flex-shrink: 0;
}
.kirby-topbar-menu ul {
  padding: 0.5rem 0;
}
.kirby-topbar-menu-button {
  display: flex;
}
.kirby-topbar-menu-button .kirby-button-text {
  opacity: 1;
}
.kirby-topbar-signals .kirby-button,
.kirby-topbar-button {
  padding: 0.75rem;
  font-size: $font-size-small;
}
.kirby-topbar-button .kirby-button-text {
  display: flex;
}
.kirby-topbar-view-button {
  flex-shrink: 0;
  display: flex;
  padding-right: 0;
  @include highlight-tabbed;
}
.kirby-topbar-view-button .kirby-icon {
  margin-right: 0.5rem;
}
.kirby-topbar-crumbs {
  flex-grow: 1;
  display: flex;
}
.kirby-topbar-crumbs a {
  position: relative;
  font-size: $font-size-small;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: none;
  opacity: 0.75;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  transition: opacity 0.3s;

  &::before {
    content: "/";
    padding: 0 0.5rem;
    opacity: 0.25;
  }
  &:focus,
  &:hover {
    opacity: 1;
  }

  @include highlight-tabbed;
}
.kirby-topbar-crumbs a:not(:last-child) {
  max-width: 15vw;
}
.kirby-topbar-breadcrumb-menu {
  flex-shrink: 0;
}
@media screen and (min-width: $breakpoint-small) {
  .kirby-topbar-crumbs a {
    display: block;
  }
  .kirby-topbar-breadcrumb-menu {
    display: none;
  }
}
.kirby-topbar-signals {
  position: absolute;
  right: 0;
  top: 0;
  background: $color-dark;
  height: 2.5rem;
}
.kirby-topbar-signals::before {
  position: absolute;
  content: "";
  top: 0;
  left: -0.5rem;
  bottom: 0;
  width: 0.5rem;
  background: -webkit-linear-gradient(
    left,
    rgba($color-dark, 0),
    rgba($color-dark, 1)
  );
}
.kirby-topbar-notification {
  font-weight: $font-weight-bold;
  line-height: 1;
}
.kirby-topbar .kirby-button[data-theme="positive"] {
  color: $color-positive-on-dark;
}
.kirby-topbar .kirby-button[data-theme="negative"] {
  color: $color-negative-on-dark;
}
.kirby-topbar .kirby-button[data-theme="negative"] .kirby-button-text {
  display: none;

  @media screen and (min-width: $breakpoint-small) {
    display: inline;
  }
}
.kirby-topbar .kirby-button[data-theme] .kirby-button-text {
  opacity: 1;
}
.kirby-topbar .kirby-dropdown-content {
  color: $color-dark;
  background: $color-white;
}
.kirby-topbar .kirby-dropdown-content hr:after {
  opacity: 0.1;
}
.kirby-topbar-menu [aria-current] .kirby-link {
  color: $color-focus;
  font-weight: 500;
}
</style>
