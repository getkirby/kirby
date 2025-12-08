<template>
	<k-block-figure
		:caption="content.caption"
		:caption-marks="captionMarks"
		:disabled="disabled"
		:empty-text="$t('field.blocks.video.placeholder') + ' …'"
		:is-empty="!video"
		empty-icon="video"
		class="k-block-type-video-figure"
		@open="open"
		@update="update"
	>
		<k-video-frame
			v-if="video"
			:controls="content.controls"
			:poster="poster"
			:url="video"
			element="div"
		/>
	</k-block-figure>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeVideo
 */
export default {
	extends: Block,
	computed: {
		captionMarks() {
			return this.field("caption", { marks: true }).marks;
		},
		poster() {
			return this.content.poster?.[0]?.url;
		},
		video() {
			if (this.content.location === "kirby") {
				return this.content.video?.[0]?.uuid ?? this.content.video?.[0]?.id;
			}

			return this.content.url;
		}
	}
};
</script>

<style>
.k-block-type-video-figure video {
	pointer-events: none;
}
</style>
