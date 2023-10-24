<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Views">
			<k-button icon="image" variant="filled" @click="screenshot('site')">
				Site
			</k-button>
			<k-button icon="image" variant="filled" @click="screenshot('languages')">
				Languages
			</k-button>
			<k-button icon="image" variant="filled" @click="screenshot('users')">
				Users
			</k-button>
		</k-lab-example>

		<portal v-if="screen" to="overlay">
			<div class="k-lab-example-screenshot" @click="screen = false">
				<iframe ref="iframe" :src="screen.url" @load="snap"></iframe>
			</div>
		</portal>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			screen: false
		};
	},
	methods: {
		async screenshot(url, name) {
			const canvas = document.createElement("canvas");
			const context = canvas.getContext("2d");
			const screenshot = document.createElement("screenshot");

			console.log(navigator);

			try {
				const captureStream = await navigator.mediaDevices.getDisplayMedia();
				screenshot.srcObject = captureStream;
				context.drawImage(screenshot, 0, 0, window.width, window.height);
				const frame = canvas.toDataURL("image/png");
				captureStream.getTracks().forEach((track) => track.stop());
				window.location.href = frame;
			} catch (err) {
				console.error("Error: " + err);
			}

			// this.screen = {
			// 	url: this.$panel.url(url),
			// 	name: name ?? url
			// };
		},
		async snap() {
			const { toPng, toJpeg } = await import(
				"https://cdn.skypack.dev/html-to-image"
			);

			await new Promise((resolve) => setTimeout(resolve, 1000));

			try {
				const image = await toJpeg(
					this.$refs.iframe.contentDocument.querySelector("body"),
					{
						width: 1024,
						height: 768,
						canvasWidth: 1024,
						canvasHeight: 768,
						skipAutoScale: true,
						pixelRatio: 2
					}
				);

				let link = document.createElement("a");

				link.download = this.screen.name + ".jpg";
				link.href = image;
				link.click();
			} catch (e) {
				console.log(e);
			}
		}
	}
};
</script>

<style>
.k-lab-example-screenshot {
	position: fixed;
	inset: 0;
	display: grid;
	place-items: center;
	box-shadow: var(--shadow-xl);
	z-index: 1000000;
	background: #fff;
}
.k-lab-example-screenshot iframe {
	width: 1024px;
	height: 768px;
	border-radius: var(--rounded);
	background: var(--color-light);
	box-shadow: var(--shadow-xl);
}
</style>
