<template>
	<div class="k-audio-file-preview">
		<audio controls preload="metadata" :src="url" />
		<k-file-preview-details :details="details" />
	</div>
</template>

<script>
/**
 * File view preview for audio files
 * @since 5.0.0
 */
export default {
	props: {
		details: Array,
		url: String
	}
};
</script>

<style>
.k-audio-file-preview {
	display: block;
}
.k-audio-file-preview audio {
	width: 100%;
}

.k-audio-file-preview audio::-webkit-media-controls-enclosure {
	border-radius: 0;
}
</style>
