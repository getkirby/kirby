<template>
  <figure class="k-block-figure">
    <k-button
      v-if="isEmpty"
      :icon="emptyIcon"
      class="k-block-figure-empty"
      @click="$emit('open')"
    >
      {{ emptyText }}
    </k-button>
    <span v-else class="k-block-figure-container" @dblclick="$emit('open')">
      <slot />
    </span>
    <figcaption v-if="caption">
      <k-writer
        :inline="true"
        :marks="captionMarks"
        :value="caption"
        @input="$emit('update', { caption: $event })"
      />
    </figcaption>
  </figure>
</template>

<script>
/**
 * @internal
 */
export default {
  inheritAttrs: false,
  props: {
    caption: String,
    captionMarks: [Boolean, Array],
    cover: {
      type: Boolean,
      default: true
    },
    isEmpty: Boolean,
    emptyIcon: String,
    emptyText: String,
    ratio: String
  },
  computed: {
    ratioPadding() {
      return this.$helper.ratio(this.ratio || "16/9");
    }
  }
};
</script>

<style>
.k-block-figure {
  cursor: pointer;
}
.k-block-figure iframe {
  border: 0;
  pointer-events: none;
  background: var(--color-black);
}
.k-block-figure figcaption {
  padding-top: .5rem;
  color: var(--color-gray-600);
  font-size: var(--text-sm);
  text-align: center;
}
.k-block-figure-empty.k-button {
  display: flex;
  width: 100%;
  height: 6rem;
  border-radius: var(--rounded-sm);
  align-items: center;
  justify-content: center;
  color: var(--color-gray-600);
  background: var(--color-background);
}
</style>
