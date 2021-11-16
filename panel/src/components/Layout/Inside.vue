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
    <main class="k-panel-view scroll-y">
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
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
}
.k-panel-inside:focus {
  outline: 0;
}
.k-panel-header {
  z-index: var(--z-navigation);
  flex-shrink: 0;
}
.k-panel-view {
  flex-grow: 1;
  padding-bottom: 6rem;
}
</style>
