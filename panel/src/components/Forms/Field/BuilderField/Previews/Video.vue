<template>
  <div class="k-block-video" @click="$emit('edit')">
    <iframe v-if="embed" :src="embed"></iframe>
  </div>
</template>

<script>
export default {
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
  margin: 1.5rem 0;
  position: relative;
  padding-bottom: 56.25%;
  cursor: pointer;
}
.k-block-video iframe {
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
</style>
