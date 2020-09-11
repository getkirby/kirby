<template>
  <div
    :data-last="last"
    :data-vertical="vertical"
    class="k-builder-block-creator"
  >
    <button type="button" class="k-builder-block-creator-toggle" @click="$emit('select')"></button>
  </div>
</template>

<script>
export default {
  props: {
    fieldsets: {
      type: Object,
      default() {
        return {}
      }
    },
    last: Boolean,
    vertical: Boolean,
  }
};
</script>

<style lang="scss">
.k-builder-block-creator {
  position: absolute;
  left: 0;
  right: 0;
  top: -.5rem;
  width: 100%;
  height: .5rem;
  opacity: 0;
  transition: opacity .2s;
}
.k-builder-block-creator:hover {
  opacity: 1;
  outline: 0;
}
.k-builder-block-creator-toggle {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.k-builder-block-creator-toggle:focus {
  outline: none;
}
.k-builder-block-creator-toggle:after {
  position: absolute;
  content: "";
  left: 0;
  right: 0;
  top: 50%;
  height: 2px;
  margin-top: -1px;
  background: $color-focus;
  box-shadow: rgba($color-focus, .5) 0 0 10px;
}
.k-builder-block-creator[data-vertical] {
  top: 0;
  right: auto;
  bottom: 0;
  left: -.5rem;
  height: auto;
  width: .5rem;
  bottom: 0;
}
.k-builder-block-creator[data-vertical] .k-builder-block-creator-toggle:after {
  left: 50%;
  right: auto;
  top: 0;
  bottom: 0;
  height: auto;
  width: 2px;
  margin-top: auto;
  margin-left: -1px;
}
.k-builder-block-creator .k-dropdown-content {
  position: absolute;
  top: 0;
  left: 50%;
}
.k-builder-block-creator[data-last] {
  top: auto;
  bottom: -.5rem;
}
.k-builder-block-creator[data-last][data-vertical] {
  top: 0;
  left: auto;
  right: -.5rem;
  bottom: 0;
}
</style>
