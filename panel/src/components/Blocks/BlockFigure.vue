<template>
  <figure class="k-block-figure" @click="$emit('open')">
    <span class="k-block-figure-container">
      <k-button
        v-if="isEmpty"
        :icon="emptyIcon"
        class="k-block-figure-empty"
      >
        {{ emptyText }}
      </k-button>
      <slot v-else />
    </span>
    <figcaption v-if="caption" v-html="caption" />
  </figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    caption: String,
    isEmpty: Boolean,
    emptyIcon: String,
    emptyText: String
  }
};
</script>

<style lang="scss">
.k-block-figure {
  cursor: pointer;
}
.k-block-figure-container {
  position: relative;
  display: block;
  overflow: hidden;
  padding-bottom: 52.65%;
  background: $color-background;
}
.k-block-figure-container > * {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  height: 100%;
  width: 100%;
  object-fit: cover;
}
.k-block-figure iframe {
  border: 0;
  pointer-events: none;
  background: $color-black;
}
.k-block-figure figcaption {
  padding-top: .5rem;
  color: $color-gray-600;
  font-size: $text-sm;
}
.k-block-figure-empty.k-button {
  display: flex;
  align-items: center;
  justify-content: center;
  color: $color-gray-600;
}
</style>
