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
		<k-frame ratio="16/9">
			<template v-if="video">
				<video
					v-if="location == 'kirby'"
					:src="video"
					:poster="poster"
					controls
				/>
				<iframe
					v-else
					:src="video"
					referrerpolicy="strict-origin-when-cross-origin"
				/>
			</template>
		</k-frame>
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
		location() {
			return this.content.location;
		},
		poster() {
			return this.content.poster?.[0]?.url;
		},
		video() {
			if (this.content.location === "kirby") {
				return this.content.video?.[0]?.url;
			}

			return this.$helper.embed.video(this.content.url ?? "", true);
		}
	}
};
</script>

<style>
.k-block-type-video-figure video {
	pointer-events: none;
}
</style>
