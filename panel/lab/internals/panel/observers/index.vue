<template>
	<k-lab-examples>
		<k-lab-example label="frames" :code="false">
			<k-text>
				<p>
					<code>window.panel.observers.resize</code> is a resize observer meant
					to subscribe to element widths/heights.
				</p>
			</k-text>

			<div ref="frame" class="frame">
				{{ frame.width }}&times;{{ frame.height }}
			</div>
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
		const frame = this.$refs.frame;
		this.$panel.observers.resize.observe(frame);

		frame.addEventListener("resize", ({ detail }) => {
			this.frame = {
				width: Math.round(detail.width),
				height: Math.round(detail.height)
			};
		});
	},
	beforeUnmount() {
		this.$panel.observers.resize.unobserve(this.$refs.frame);
	}
};
</script>

<style>
.k-lab-example .frame {
	width: 40vw;
	height: 30vh;
	background: var(--color-yellow-300);
	margin-top: var(--spacing-6);
	display: grid;
	place-items: center;
}
</style>
