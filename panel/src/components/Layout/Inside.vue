<template>
  <k-panel class="k-panel-inside" tabindex="0">
    <header class="k-panel-header">
      <k-topbar
        :breadcrumb="$view.breadcrumb"
        :license="$license"
        :menu="$menu"
        :view="$view"
      />
    </header>
    <main class="k-panel-view">
      <slot />
    </main>
    <slot name="footer" />
  </k-panel>
</template>

<script>
export default {
  inheritAttrs: false
};
</script>

<style>
.k-panel-inside {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.k-panel-inside:focus {
  outline: 0;
}
.k-panel-header {
  z-index: var(--z-navigation);
  flex-shrink: 0;
  position: sticky;
  top: 0;
}
.k-panel-view {
  flex-grow: 1;
}
</style>
