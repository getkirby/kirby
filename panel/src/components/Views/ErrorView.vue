<template>
  <component :is="`k-${layout}`">
    <k-view class="k-error-view">
      <div class="k-error-view-content">
        <k-text>
          <p>
            <k-icon class="k-error-view-icon" type="alert" />
          </p>
          <slot>
            <p>
              {{ error }}
            </p>
          </slot>
        </k-text>
      </div>
    </k-view>
  </component>
</template>

<script>
export default {
  props: {
    error: String,
    layout: String
  }
};
</script>

<style lang="scss">
.k-error-view {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-error-view-content {
  line-height: 1.5em;
  max-width: 25rem;
  text-align: center;
}
.k-error-view-icon {
  color: $color-negative;
  display: inline-block;
}
.k-error-view-content p:not(:last-child) {
  margin-bottom: 0.75rem;
}
</style>
