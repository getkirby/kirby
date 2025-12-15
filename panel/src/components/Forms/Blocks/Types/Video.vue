<template>
	<k-block-figure
		:caption="content.caption"
		:caption-marks="captionMarks"
		:disabled="disabled"
		:empty-text="$t('field.blocks.video.placeholder') + ' …'"
		:is-empty="isEmpty"
		empty-icon="video"
		class="k-block-type-video-figure"
		@open="open"
		@update="update"
	>
		<k-video-frame
			:controls="content.controls"
			:file="file"
			:poster="poster"
			:url="url"
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
		file() {
			if (this.isInternal) {
				return this.content.video?.[0]?.uuid ?? this.content.video?.[0]?.id;
			}

			return undefined;
		},
		isEmpty() {
			return this.isInternal ? !this.file : !this.url;
		},
		isInternal() {
			return this.content.location === "kirby";
		},
		poster() {
			return this.content.poster?.[0]?.url;
		},
		url() {
			if (!this.isInternal) {
				return this.content.url;
			}

			return undefined;
		}
	}
};
</script>

<style>
.k-block-type-video-figure video {
	pointer-events: none;
}
</style>
