<template>
  <span class="k-loader flex items-center justify-center">
    <svg viewBox="0 0 16 18">
      <path d="M8,0 L16,4.50265232 L16,13.5112142 L8,18.0138665 L0,13.5112142 L0,4.50265232 L8,0 Z M2.10648757,5.69852516 L2.10648757,12.3153414 L8,15.632396 L13.8935124,12.3153414 L13.8935124,5.69852516 L8,2.38147048 L2.10648757,5.69852516 Z" />
    </svg>
  </span>
</template>

<style lang="scss">
.k-loader {
  width: 18px;
  height: 18px;
  z-index: 1;
}
.k-loader svg {
  width: 18px;
  height: 18px;
  animation: Spin .9s linear infinite;
}
.k-loader path {
  fill: currentColor;
}
</style>
