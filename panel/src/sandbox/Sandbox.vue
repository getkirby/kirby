<template>
  <div class="k-sandbox">
    <nav class="k-sandbox-menu">

    </nav>
    <div class="k-sandbox-preview">
      <iframe class="k-sandbox-iframe" src="/sandbox/something"></iframe>
    </div>
  </div>
</template>

<style lang="scss">
.k-sandbox {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: grid;
  grid-template-columns: 15rem auto;
  height: 100%;
}
.k-sandbox-menu {
  background: $color-white;
}
.k-sandbox-preview {
  position: relative;
}
.k-sandbox-iframe {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  border: 0;
}
</style>
