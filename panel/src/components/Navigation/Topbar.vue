<template>
  <header v-if="isVisible" class="k-topbar">
    <k-view>
      <div class="k-topbar-wrapper">
        <k-dropdown class="k-topbar-menu">
          <k-button icon="bars" class="k-topbar-button k-topbar-menu-button" @click="$refs.menu.toggle()">
            <k-icon type="angle-down" />
          </k-button>
          <k-dropdown-content ref="menu" class="k-topbar-menu">
            <ul>
              <li
                v-for="(view, viewName) in views"
                v-if="view.menu"
                :aria-current="$route.meta.view === viewName"
                :key="'menu-item-' + viewName"
              >
                <k-dropdown-item
                  :disabled="$permissions.access[viewName] === false"
                  :icon="view.icon"
                  :link="view.link"
                >
                  {{ $t(`view.${viewName}`, view.label) }}
                </k-dropdown-item>
              </li>
              <li><hr></li>
              <li :aria-current="$route.meta.view === 'account'">
                <k-dropdown-item icon="account" link="/account">
                  {{ $t("view.account") }}
                </k-dropdown-item>
              </li>
              <li><hr></li>
              <li>
                <k-dropdown-item icon="logout" link="/logout">
                  {{ $t("logout") }}
                </k-dropdown-item>
              </li>
            </ul>
          </k-dropdown-content>
        </k-dropdown>

        <k-link
          v-tab
          v-if="view"
          :to="view.link"
          class="k-topbar-button k-topbar-view-button"
        >
          <k-icon :type="view.icon" /> {{ $t(`view.${$store.state.view}`, view.label) }}
        </k-link>

        <k-dropdown v-if="$store.state.breadcrumb.length > 1" class="k-topbar-breadcrumb-menu">
          <k-button class="k-topbar-button" @click="$refs.crumb.toggle()">
            …
            <k-icon type="angle-down" />
          </k-button>

          <k-dropdown-content ref="crumb">
            <k-dropdown-item :icon="view.icon" :link="view.link">
              {{ $t(`view.${$store.state.view}`, view.label) }}
            </k-dropdown-item>
            <k-dropdown-item
              v-for="(crumb, index) in $store.state.breadcrumb"
              :key="'crumb-' + index + '-dropdown'"
              :icon="view.icon"
              :link="crumb.link"
            >
              {{ crumb.label }}
            </k-dropdown-item>
          </k-dropdown-content>
        </k-dropdown>

        <nav class="k-topbar-crumbs">
          <k-link
            v-tab
            v-for="(crumb, index) in $store.state.breadcrumb"
            :key="'crumb-' + index"
            :to="crumb.link"
          >
            {{ crumb.label }}
          </k-link>
        </nav>

        <div class="k-topbar-signals">
          <k-button
            v-if="notification"
            class="k-topbar-notification"
            theme="positive"
            @click="$store.dispatch('notification/close')"
          >
            {{ notification.message }}
          </k-button>
          <k-form-changes />
          <k-button icon="search" @click="$store.dispatch('search', true)" />
        </div>
      </div>
    </k-view>
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
.k-topbar {
  position: relative;
  color: $color-white;
  flex-shrink: 0;
  height: 2.5rem;
  background: $color-dark;
}
.k-topbar-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: -0.75rem;
  margin-right: -0.75rem;
}
.k-topbar-menu {
  flex-shrink: 0;
}
.k-topbar-menu ul {
  padding: 0.5rem 0;
}
.k-topbar-menu-button {
  display: flex;
}
.k-topbar-menu-button .k-button-text {
  opacity: 1;
}
.k-topbar-signals .k-button,
.k-topbar-button {
  padding: 0.75rem;
  font-size: $font-size-small;
}
.k-topbar-button .k-button-text {
  display: flex;
}
.k-topbar-view-button {
  flex-shrink: 0;
  display: flex;
  padding-right: 0;
  @include highlight-tabbed;
}
.k-topbar-view-button .k-icon {
  margin-right: 0.5rem;
}
.k-topbar-crumbs {
  flex-grow: 1;
  display: flex;
}
.k-topbar-crumbs a {
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
.k-topbar-crumbs a:not(:last-child) {
  max-width: 15vw;
}
.k-topbar-breadcrumb-menu {
  flex-shrink: 0;
}
@media screen and (min-width: $breakpoint-small) {
  .k-topbar-crumbs a {
    display: block;
  }
  .k-topbar-breadcrumb-menu {
    display: none;
  }
}
.k-topbar-signals {
  position: absolute;
  right: 0;
  top: 0;
  background: $color-dark;
  height: 2.5rem;
}
.k-topbar-signals::before {
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
.k-topbar-signals .k-button {
  line-height: 1;
}

.k-topbar-notification {
  font-weight: $font-weight-bold;
  line-height: 1;
}
.k-topbar .k-button[data-theme="positive"] {
  color: $color-positive-on-dark;
}
.k-topbar .k-button[data-theme="negative"] {
  color: $color-negative-on-dark;
}
.k-topbar .k-button[data-theme="negative"] .k-button-text {
  display: none;

  @media screen and (min-width: $breakpoint-small) {
    display: inline;
  }
}
.k-topbar .k-button[data-theme] .k-button-text {
  opacity: 1;
}
.k-topbar .k-dropdown-content {
  color: $color-dark;
  background: $color-white;
}
.k-topbar .k-dropdown-content hr:after {
  opacity: 0.1;
}
.k-topbar-menu [aria-current] .k-link {
  color: $color-focus;
  font-weight: 500;
}
</style>
