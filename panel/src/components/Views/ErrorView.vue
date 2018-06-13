<template>
  <kirby-view class="kirby-error-view">
    <div class="kirby-error-view-content">
      <kirby-text>
        <p>
          <kirby-icon class="kirby-error-view-icon" type="alert" />
        </p>
        <p>
          <slot />
        </p>
      </kirby-text>
    </div>
  </kirby-view>
</template>

<style lang="scss">
.kirby-error-view {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kirby-error-view-content {
  line-height: 1.5em;
  max-width: 25rem;
  text-align: center;
}
.kirby-error-view-icon {
  color: $color-negative;
  display: inline-block;
}
.kirby-error-view-content p:not(:last-child) {
  margin-bottom: 0.75rem;
}
</style>
