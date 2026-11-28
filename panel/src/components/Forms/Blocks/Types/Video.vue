<template>
	<k-block-figure
		:caption="content.caption"
		:caption-dir="captionField.dir"
		:caption-lang="captionField.lang"
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
			:poster="resolvedPoster"
			:url="url"
			element="div"
		/>
	</k-block-figure>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeVideo
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	extends: Block,
	data() {
		return {
			resolvedPoster: undefined
		};
	},
	computed: {
		captionField() {
			return this.field("caption", { marks: true });
		},
		captionMarks() {
			return this.captionField.marks;
		},
		file() {
			if (this.isInternal) {
				return this.content.video?.[0];
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
			if (this.isInternal) {
				return this.content.poster?.[0];
			}

			return undefined;
		},
		url() {
			if (!this.isInternal) {
				return this.content.url;
			}

			return undefined;
		}
	},
	watch: {
		poster: {
			handler: "fetchPoster",
			immediate: true
		}
	},
	methods: {
		async fetchPoster() {
			const poster = this.poster;
			let url;

			// load the URL for the poster's file UUID from the request endpoint
			if (poster) {
				url = (await this.$helper.items("items/files", poster))?.url;

				// the poster might have changed while the request was in flight
				if (this.poster !== poster) {
					return;
				}
			}

			this.resolvedPoster = url;
		}
	}
};
</script>

<style>
.k-block-type-video-figure video {
	pointer-events: none;
}
</style>
