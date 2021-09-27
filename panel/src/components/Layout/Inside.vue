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
  </k-panel>
</template>

<script>
export default {
  inheritAttrs: false
};
</script>

<style>
.k-panel-header {
  position: fixed;
  top: 0;
  inset-inline: 0;
  z-index: var(--z-navigation);
}
.k-panel-view {
  padding-top: 2.5rem;
  padding-bottom: 6rem;
}
</style>
