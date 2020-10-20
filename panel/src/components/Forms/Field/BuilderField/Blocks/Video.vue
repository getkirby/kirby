<template>
  <figure v-if="embed" class="k-block-video-figure" @click="$emit('open')">
    <span class="k-block-video-frame">
      <iframe :src="embed"></iframe>
    </span>
    <figcaption v-if="content.caption" v-html="content.caption" />
  </figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  },
  mounted() {
    if (this.embed === false) {
      this.$emit("edit");
    }
  },
  computed: {
    embed() {

      var url = this.content.url;

      if (!url) {
        return false;
      }

      var youtubePattern = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
      var youtubeMatch = url.match(youtubePattern);

      if (youtubeMatch) {
        return "https://www.youtube.com/embed/" + youtubeMatch[2];
      }

      var vimeoPattern = /vimeo\.com\/([0-9]+)/;
      var vimeoMatch = url.match(vimeoPattern);

      if (vimeoMatch) {
        return "https://player.vimeo.com/video/" + vimeoMatch[1];
      }

      return false;

    }
  }
};
</script>

<style lang="scss">
.k-block-video {
  padding: 1.5rem 0;
}
.k-block-video-figure {
  cursor: pointer;
}
.k-block-video-frame {
  display: block;
  position: relative;
  padding-bottom: 56.25%;
}
.k-block-video-frame iframe {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  border: 0;
  pointer-events: none;
  background: $color-black;
}
.k-block-video-figure figcaption {
  padding-top: .5rem;
  color: $color-gray-600;
  font-size: $text-sm;
}
</style>
