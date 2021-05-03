<template>
  <k-block-figure
    :caption="content.caption"
    :caption-marks="captionMarks"
    :empty-text="$t('field.blocks.video.placeholder') + ' …'"
    :is-empty="!video"
    empty-icon="video"
    @open="open"
    @update="update"
  >
    <k-aspect-ratio ratio="16/9">
      <iframe v-if="video" :src="video" />
    </k-aspect-ratio>
  </k-block-figure>
</template>

<script>
/**
 * @displayName BlockTypeVideo
 * @internal
 */
export default {
  computed: {
    captionMarks() {
      return this.field("caption", { marks: true }).marks;
    },
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
