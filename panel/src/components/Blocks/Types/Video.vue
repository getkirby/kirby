<template>
  <k-block-figure
    :caption="content.caption"
    :is-empty="!video"
    empty-icon="video"
    empty-text="Enter a video URL …"
    v-on="$listeners"
  >
    <iframe v-if="video" :src="video" />
  </k-block-figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  },
  computed: {
    video() {

      var url = this.content.url;

      if (!url) {
        return false;
      }

      var youtubePattern = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
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
</style>
