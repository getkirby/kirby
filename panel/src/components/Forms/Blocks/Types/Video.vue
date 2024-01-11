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
		<k-frame ratio="16/9">
			<iframe
				v-if="video"
				:src="video"
				referrerpolicy="strict-origin-when-cross-origin"
			/>
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
		video() {
			return this.$helper.embed.video(this.content.url ?? "", true);
		}
	}
};
</script>
