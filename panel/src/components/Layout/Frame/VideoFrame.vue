<template>
	<k-frame v-if="resolvedUrl" v-bind="$props">
		<video
			v-if="isInternal"
			:controls="controls"
			:poster="poster"
			:src="resolvedUrl"
			class="k-video"
		/>
		<iframe
			v-else
			:src="resolvedUrl"
			class="k-video"
			referrerpolicy="strict-origin-when-cross-origin"
		/>
	</k-frame>
</template>

<script>
import { props as FrameProps } from "./Frame.vue";

export const props = {
	mixins: [FrameProps],
	props: {
		controls: Boolean,
		element: {
			default: "figure"
		},
		poster: String,
		ratio: {
			default: "16/9"
		},
		url: String
	}
};

/**
 * Use <k-video-frame> to display a video from an external URL
 * or internal file UUID in a fixed ratio with background etc.
 *
 * @since 6.0.0
 * @example <k-video-frame src="file://my-video" ratio="16/9" />
 */
export default {
	mixins: [props],
	data() {
		return {
			resolvedUrl: null
		};
	},
	computed: {
		isInternal() {
			return this.url?.startsWith("file://") === true;
		}
	},
	watch: {
		url: {
			handler: "fetch",
			immediate: true
		}
	},
	methods: {
		async fetch() {
			if (!this.url) {
				this.resolvedUrl = null;
				return;
			}

			if (this.isInternal === false) {
				this.resolvedUrl = this.$helper.embed.video(this.url, true);
				return;
			}

			// if internal file, load data for file UUID from request endpoint
			const data = await await this.$panel.get("items/files", {
				query: { items: this.url }
			});

			this.resolvedUrl = data.items[0]?.url;
		}
	}
};
</script>
