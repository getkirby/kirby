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
      <iframe
        v-if="video"
        :src="video"
        referrerpolicy="strict-origin-when-cross-origin"
      />
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
      return this.$helper.embed.video(this.content.url, true);
    }
  }
};
</script>
