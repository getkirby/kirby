<template>
  <k-view class="k-error-view" align="center">
    <k-text class="k-error-view-content text-center">
      <k-icon
        class="k-error-view-icon mb-3"
        color="red"
        type="alert"
      />
      <p>
        <slot />
      </p>
    </k-text>
  </k-view>
</template>

<style lang="scss">
.k-error-view-content {
  max-width: 25rem;
}
</style>
