<template>
	<img
		:alt="alt ?? ''"
		:sizes="sizes"
		:src="src"
		:srcset="srcset"
		@load="onLoad"
	/>
</template>

<script>
/**
 * @since 6.0.0
 */
export default {
	props: {
		alt: String,
		fit: String,
		src: String,
		srcset: String
	},
	data() {
		return {
			sizes: null
		};
	},
	watch: {
		fit() {
			this.measure();
		}
	},
	beforeUnmount() {
		this.$panel.observers.frames.unobserve(this.$el.parentElement);
	},
	methods: {
		measure(frame) {
			frame ??= this.$el.parentElement.getBoundingClientRect();

			const fh = frame.height ?? 0;
			const fw = frame.width ?? 0;
			const iw = this.$el.naturalWidth;
			const ih = this.$el.naturalHeight;

			if (iw === 0 || ih === 0) {
				return;
			}

			const fit = this.fit === "cover" ? "max" : "min";
			const scale = Math[fit](fw / iw, fh / ih);
			this.sizes = Math.round((iw * scale) / 50) * 50 + "px";
		},
		onLoad() {
			this.$panel.observers.frames.observe(this.$el.parentElement);
			this.$el.parentElement.addEventListener("resize", (e) =>
				this.measure(e.detail)
			);
		}
	}
};
</script>
