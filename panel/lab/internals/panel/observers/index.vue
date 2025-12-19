<template>
	<k-lab-examples>
		<k-lab-example label="frames" :code="false">
			<k-text>
				<p>
					<code>window.panel.observers.frames</code> is a resize observer meant
					for frame widths/heights. The width/height gets rounded to the nearest
					50 pixels.
				</p>
			</k-text>

			<k-frame ref="frame" back="yellow-300" ratio="2/1">
				{{ frame.width }}&times;{{ frame.height }}
			</k-frame>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			frame: {}
		};
	},
	mounted() {
		const frame = this.$refs.frame.$el;
		this.$panel.observers.frames.observe(frame);

		frame.addEventListener("resize", (e) => {
			this.frame = e.detail;
		});
	},
	beforeUnmount() {
		this.$panel.observers.frames.unobserve(this.$refs.frame.$el);
	}
};
</script>

<style>
.k-lab-example .k-frame {
	max-width: 40%;
	margin-top: var(--spacing-6);
}
</style>
