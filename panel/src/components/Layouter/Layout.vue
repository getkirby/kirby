<template>
  <div class="k-layout-section" :data-current="currentLayout">
    <div
      v-for="block in blocks"
      :key="block.id"
      :data-current="currentBlock == block.id"
      class="k-layout-block"
      tabindex="0"
      @click="$emit('edit', { block, tab: null })"
    >
      <template v-if="block.type === 'heading'">
        <div class="k-layout-block-heading">{{ block.content.text }}</div>
      </template>
      <template v-else-if="block.type === 'bodytext'">
        <div class="k-layout-block-text">{{ block.content.text }}</div>
      </template>
      <template v-else-if="block.type === 'cta'">
        <div class="k-layout-block-cta">{{ block.content.text }}</div>
      </template>
      <template v-else-if="block.type === 'gallery'">
        <div class="k-layout-block-gallery">
          <ul>
            <li v-for="image in block.content.images">
              <figure>
                <span class="k-layout-block-gallery-image"><img :src="image.url" /></span>
                <figcaption>
                  <span class="k-layout-block-gallery-filename">{{ image.filename }}</span>
                  <span class="k-layout-block-gallery-type">{{ image.type }}</span>
                </figcaption>
              </figure>
            </li>
          </ul>
        </div>
      </template>
      <template v-else-if="block.type === 'quote'">
        <div class="k-layout-block-quote">
          <blockquote>{{ block.content.text }}</blockquote>
          <cite>{{ block.content.citation }}</cite>
        </div>
      </template>
      <template v-else>
        <div class="k-layout-block-generic">
          {{ block.type }}
        </div>
      </template>

    </div>
  </div>
</template>

<script>
export default {
  props: {
    blocks: Array,
    currentLayout: Boolean,
    currentBlock: String
  }
}
</script>

<style lang="scss">

.k-layout-section {
  padding: 0;
}
.k-layout-block {
  cursor: pointer;
  padding: 0rem 1.5rem;
}
.k-layout-block:focus {
  outline: 0;
}
.k-layout-block[data-current] {
  background: rgba($color-blue-200, .25);
}
.k-layout-block:last-of-type {
  margin-bottom: 0;
}
.k-layout-block-generic {
  background: $color-background;
  font-size: $text-xs;
  border-radius: $rounded;
}
.k-layout-block-heading {
  font-size: $text-base;
  font-weight: $font-bold;
  line-height: 1.325em;
  padding: .5rem 0;
}
.k-layout-block-text {
  font-size: $text-sm;
  white-space: pre-wrap;
  line-height: 1.5em;
  padding: .5rem 0;
}
.k-layout-block-cta {
  font-size: $text-xs;
  font-weight: $font-bold;
  border: 2px solid #000;
  display: inline-flex;
  padding: .25rem 1rem;
  border-radius: 2rem;
}
.k-layout-block-gallery {
  padding: .75rem 0;
}
.k-layout-block-gallery ul {
  display: grid;
  grid-gap: .75rem;
  grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
  min-width: 0;
}
.k-layout-block-gallery figure {
  display: flex;
  align-items: center;
  border-radius: $rounded-sm;
  overflow: hidden;
  background: $color-gray-100;
  border: 1px solid $color-gray-300;
  min-width: 0;
}
.k-layout-block-gallery figcaption {
  padding: .5rem;
  font-size: $text-xs;
  overflow: hidden;
}
.k-layout-block-gallery figcaption span {
  display: block;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.k-layout-block-gallery-filename {
  font-weight: $font-bold !important;
}
.k-layout-block-gallery-type {
  color: $color-gray-500;
}
.k-layout-block-gallery-image {
  display: block;
  margin: .25rem;
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  position: relative;
  background: $color-black;
}
.k-layout-block-gallery img {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  object-fit: contain;
  height: 100%;
  width: 100%;
}

.k-layout-block-quote {
  font-size: $text-lg;
  padding: .75rem 0;
}
.k-layout-block-quote blockquote {
  font-style: italic;
  margin-bottom: .75rem;
  line-height: 1.5em;
  border-left: 2px solid #000;
  padding-left: .75rem;
}
.k-layout-block-quote cite {
  font-style: normal;
  color: $color-gray-500;
  font-size: $text-sm;
}

</style>
