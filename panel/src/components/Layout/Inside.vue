<template>
  <k-panel class="k-panel-inside" tabindex="0">
    <!-- Header -->
    <header class="k-panel-header">
      <k-topbar
        :breadcrumb="$view.breadcrumb"
        :license="$license"
        :menu="$menu"
        :view="$view"
        @search="$refs.search.open()"
      />

      <k-search
        ref="search"
        :type="$view.search || 'pages'"
        :types="$searches"
      />
    </header>

    <!-- Main view -->
    <main class="k-panel-view">
      <slot />
    </main>

  </k-panel>
</template>

<script>
export default {
  inheritAttrs: false
};
</script>

<style>
.k-panel-view {
  padding-top: 2.5rem;
  padding-bottom: 6rem;
}
.k-panel-header {
  position: fixed;
  top: 0;
  inset-inline: 0;
  z-index: var(--z-navigation);
}
</style>
