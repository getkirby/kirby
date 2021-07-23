<template>
  <div class="k-topbar">
    <k-view>
      <div class="k-topbar-wrapper">
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
            theme="light"
            class="k-topbar-menu"
          />
        </k-dropdown>

        <!-- Breadcrumb -->
        <k-breadcrumb
          :crumbs="breadcrumb"
          :view="view"
          class="k-topbar-breadcrumb"
        />

        <div class="k-topbar-signals">
          <!-- notifications -->
          <template v-if="notification">
            <k-button
              class="k-topbar-notification k-topbar-signals-button"
              theme="positive"
              @click="$store.dispatch('notification/close')"
            >
              {{ notification.message }}
            </k-button>
          </template>

          <!-- registration -->
          <template v-else-if="!license">
            <div class="k-registration">
              <p>{{ $t('license.unregistered') }}</p>
              <k-button
                :responsive="true"
                :tooltip="$t('license.unregistered')"
                class="k-topbar-signals-button"
                icon="key"
                @click="$dialog('registration')"
              >
                {{ $t('license.register') }}
              </k-button>
              <k-button
                :responsive="true"
                class="k-topbar-signals-button"
                link="https://getkirby.com/buy"
                target="_blank"
                icon="cart"
              >
                {{ $t('license.buy') }}
              </k-button>
            </div>
          </template>

          <!-- unsaved changes indicator -->
          <k-form-indicator />

          <!-- search -->
          <k-button
            :tooltip="$t('search')"
            class="k-topbar-signals-button"
            icon="search"
            @click="$emit('search')"
          />
        </div>
      </div>
    </k-view>
  </div>
</template>

<script>
export default {
  props: {
    breadcrumb: Array,
    license: Boolean,
    menu: Array,
    title: String,
    view: Object,
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
  position: relative;
  color: var(--color-white);
  flex-shrink: 0;
  height: 2.5rem;
  line-height: 1;
  background: var(--color-gray-900);
}
.k-topbar-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-inline: -0.75rem;
}

.k-topbar-menu {
  flex-shrink: 0;
}
.k-topbar-menu ul {
  padding: .5rem 0;
}
.k-topbar-menu-button {
  display: flex;
  align-items: center;
}
.k-topbar-menu-button .k-button-text {
  opacity: 1;
}
.k-topbar-menu .k-link[aria-current] {
  color: var(--color-focus);
  font-weight: 500;
}
.k-topbar-signals-button,
.k-topbar-button {
  padding: .75rem;
  line-height: 1;
  font-size: var(--text-sm);
}
.k-topbar-signals .k-button .k-button-text {
  opacity: 1;
}
.k-topbar-button .k-button-text {
  display: flex;
  opacity: 1;
}
.k-topbar-view-button {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  padding-inline-end: 0;
}
.k-topbar-view-button .k-icon {
  margin-inline-end: .5rem;
}

.k-topbar-signals {
  position: absolute;
  inset-block-start: 0;
  inset-inline-end: 0;
  background: var(--color-gray-900);
  height: 2.5rem;
  display: flex;
  align-items: center;
}
.k-topbar-signals::before {
  position: absolute;
  content: "";
  inset-block-start: -0.5rem;
  inset-block-end: 0;
  width: .5rem;
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
.k-topbar .k-button[data-theme] .k-button-text {
  opacity: 1;
}
.k-topbar .k-button[data-theme="positive"] {
  color: var(--color-positive-light);
}
.k-topbar .k-button[data-theme="negative"] {
  color: var(--color-negative-light);
}
.k-topbar .k-button[data-theme="negative"] .k-button-text {
  display: none;
}

@media screen and (min-width: 30em){
  .k-topbar .k-button[data-theme="negative"] .k-button-text {
    display: inline;
  }
}

.k-registration {
  display: inline-block;
  margin-inline-end: 1rem;
  display: flex;
  align-items: center;
}
.k-registration p {
  color: var(--color-negative-light);
  font-size: var(--text-sm);
  margin-inline-end: 1rem;
  font-weight: 600;
  display: none;
}
@media screen and (min-width: 90em) {
  .k-registration p {
    display: block;
  }
}
.k-registration .k-button {
  color: var(--color-white);
}
</style>
