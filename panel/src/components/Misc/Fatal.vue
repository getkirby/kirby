<template>
	<k-overlay :visible="true" class="k-fatal">
		<div class="k-fatal-box">
			<div data-theme="negative" class="k-notification">
				<p>The JSON response could not be parsed</p>
				<k-button icon="cancel" @click.stop="$panel.notification.close()" />
			</div>
			<iframe ref="iframe" class="k-fatal-iframe" />
		</div>
	</k-overlay>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		html: String
	},
	mounted() {
		try {
			let doc = this.$refs.iframe.contentWindow.document;
			doc.open();
			doc.write(this.html);
			doc.close();
		} catch (e) {
			console.error(e);
		}
	}
};
</script>

<style>
.k-fatal[open] {
	background: var(--overlay-color-back);
	padding: var(--spacing-6);
}
.k-fatal-box {
	position: relative;
	width: 100%;
	box-shadow: var(--dialog-shadow);
	border-radius: var(--dialog-rounded);
	line-height: 1;
	height: calc(100vh - 3rem);
	height: calc(100dvh - 3rem);
	display: flex;
	flex-direction: column;
	overflow: hidden;
}
.k-fatal-iframe {
	border: 0;
	width: 100%;
	flex-grow: 1;
	background: var(--color-white);
	padding: var(--spacing-3);
}
</style>
