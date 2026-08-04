<template>
	<k-frame v-if="url || resolvedUrl" v-bind="$props">
		<iframe
			v-if="url"
			:src="$helper.embed.video(url, true)"
			:title="$t('video')"
			class="k-video"
			referrerpolicy="strict-origin-when-cross-origin"
		/>
		<video
			v-else
			:controls="controls"
			:poster="poster"
			:src="resolvedUrl"
			class="k-video"
		/>
	</k-frame>
</template>

<script>
import { props as FrameProps } from "./Frame.vue";

export const props = {
	mixins: [FrameProps],
	props: {
		/**
		 * Whether to show the video controls
		 */
		controls: Boolean,
		element: {
			default: "figure"
		},
		/**
		 * File ID/UUID (can be used instead of `url`)
		 */
		file: String,
		/**
		 * URL of poster image
		 */
		poster: String,
		ratio: {
			default: "16/9"
		},
		/**
		 * URL to video file
		 */
		url: String
	}
};

/**
 * Use <k-video-frame> to display a video from an external URL
 * or internal file UUID in a fixed ratio with background etc.
 *
 * @example
 * <k-video-frame src="file://my-video" ratio="16/9" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	mixins: [props],
	data() {
		return {
			resolvedUrl: null
		};
	},
	watch: {
		file: {
			handler: "fetch",
			immediate: true
		}
	},
	methods: {
		async fetch() {
			const file = this.file;
			let url = null;

			// if internal file, load data for file UUID from request endpoint
			if (file) {
				url = (await this.$helper.items("items/files", file))?.url;

				// the file might have changed while the request was in flight
				if (this.file !== file) {
					return;
				}
			}

			this.resolvedUrl = url;
		}
	}
};
</script>
