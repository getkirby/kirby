<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following embed helpers in your Vue components through
				<code>this.$helper.embed</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.embed.youtube()" script="youtube">
			<k-text>
				<p>Builds a YouTube embed URL:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.embed.youtube(url, doNotTrack): string|false</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="youtube" @input="youtube = $event" />
					<k-input
						type="toggle"
						:text="['Allow tracking', 'Do not track']"
						:value="youtubeNotTrack"
						@input="youtubeNotTrack = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.embed.youtube(youtube, youtubeNotTrack) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.embed.vimeo()" script="vimeo">
			<k-text>
				<p>Builds a Vimeo embed URL:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.embed.vimeo(url, doNotTrack): string|false</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="vimeo" @input="vimeo = $event" />
					<k-input
						type="toggle"
						:text="['Allow tracking', 'Do not track']"
						:value="vimeoNotTrack"
						@input="vimeoNotTrack = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.embed.vimeo(vimeo, vimeoNotTrack) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.embed.video()">
			<k-text>
				<p>Builds an embed URL for the given video URL:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.embed.video(url, doNotTrack): string|false</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code>{{ youtube }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.embed.video(youtube, youtubeNotTrack) }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code>{{ vimeo }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code>{{ $helper.embed.video(vimeo, vimeoNotTrack) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: youtube */
export const youtube = {
	data() {
		return {
			youtube: "https://youtu.be/5XUHlSWG92I",
			youtubeNotTrack: false
		};
	}
};
/** @script-end */

/** @script: vimeo */
export const vimeo = {
	data() {
		return {
			vimeo: "https://vimeo.com/512270847",
			vimeoNotTrack: false
		};
	}
};
/** @script-end */

export default {
	mixins: [youtube, vimeo]
};
</script>
