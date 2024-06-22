<template>
	<div class="k-file-audio-preview">
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
		details: {
			default: () => [],
			type: Array
		},
		url: String
	}
};
</script>

<style>
.k-file-audio-preview audio {
	width: 100%;
}
</style>
