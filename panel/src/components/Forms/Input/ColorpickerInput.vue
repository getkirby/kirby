<template>
	<div
		:style="{
			'--h': hsl.h,
			'--s': hsl.s,
			'--l': hsl.l,
			'--a': hsl.a
		}"
		class="k-colorpicker-input"
	>
		<k-coords-input :value="coords" @input="setCoords($event)" />
		<k-hue-input :value="color.h" @input="setHue($event)" />
		<k-alpha-input v-if="alpha" :value="color.a" @input="setAlpha($event)" />
		<k-coloroption-input
			v-if="options"
			:format="format"
			:options="options"
			:value="value"
			@input="$emit('input', $event)"
		/>
	</div>
</template>

<script>
export default {
	props: {
		alpha: {
			default: true,
			type: Boolean
		},
		format: {
			default: "hex",
			type: String,
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		options: {
			type: Array
		},
		value: {
			type: [Object, String]
		}
	},
	data() {
		return {
			color: {
				h: 0,
				s: 0,
				v: 1,
				a: 1
			},
			formatted: null
		};
	},
	computed: {
		coords() {
			return {
				x: this.color.s * 100,
				y: (1 - this.color.v) * 100
			};
		},
		hsl() {
			try {
				const hsl = this.$library.colors.convert(this.color, "hsl");
				return {
					h: hsl.h,
					s: (hsl.s * 100).toFixed() + "%",
					l: (hsl.l * 100).toFixed() + "%",
					a: hsl.a
				};
			} catch {
				return {
					h: 0,
					s: "0%",
					l: "0%",
					a: 1
				};
			}
		}
	},
	watch: {
		value: {
			handler(newValue, oldValue) {
				if (newValue === oldValue || newValue === this.formatted) {
					return;
				}

				const color = this.$library.colors.parseAs(newValue ?? "", "hsv");

				if (color) {
					this.formatted = this.$library.colors.toString(color, this.format);
					this.color = color;
				} else {
					this.formatted = null;
					this.color = {
						h: 0,
						s: 0,
						v: 1,
						a: 1
					};
				}
			},
			immediate: true
		}
	},
	methods: {
		between(value, min, max) {
			return Math.min(Math.max(value, min), max);
		},
		emit() {
			this.formatted = this.$library.colors.toString(this.color, this.format);
			return this.$emit("input", this.formatted);
		},
		setAlpha(alpha) {
			this.color.a = this.alpha ? this.between(Number(alpha), 0, 1) : 1;
			this.emit();
		},
		setCoords(coords) {
			const x = Math.round(coords.x);
			const y = Math.round(coords.y);

			this.color.s = this.between(x / 100, 0, 1);
			this.color.v = this.between(1 - y / 100, 0, 1);
			this.emit();
		},
		setHue(hue) {
			this.color.h = this.between(Number(hue), 0, 360);
			this.emit();
		}
	}
};
</script>

<style>
:root {
	--color-preview-rounded: var(--rounded-sm);
	--color-preview-size: 1.5rem;
	--color-preview-darkness: 0%;
}

.k-colorpicker-input {
	--h: 0;
	--s: 0%;
	--l: 0%;
	--a: 1;
	--range-thumb-size: 0.75rem;
	--range-track-height: 0.75rem;
	display: flex;
	flex-direction: column;
	gap: var(--spacing-3);
	width: max-content;
}

.k-colorpicker-input .k-coords-input {
	border-radius: var(--rounded-sm);
	aspect-ratio: 1/1;
	background: linear-gradient(to bottom, transparent, #000),
		linear-gradient(to right, #fff, hsl(var(--h), 100%, 50%));
}

.k-colorpicker-input .k-alpha-input {
	color: hsl(var(--h), var(--s), var(--l));
}

.k-colorpicker-input .k-coloroption-input {
	--color-preview-size: 100%;
	--color-preview-darkness: 100%;
	grid-template-columns: repeat(6, 1fr);
}

.k-color-preview {
	aspect-ratio: 1/1;
	position: relative;
	display: inline-block;
	color: transparent;
	background: var(--pattern-light);
	border-radius: var(--color-preview-rounded);
	overflow: hidden;
	width: var(--color-preview-size);
	background-clip: padding-box;
}

.k-color-preview::after {
	border-radius: calc(var(--color-frame-rounded) - 1px);
	box-shadow: 0 0 0 1px inset hsla(0, 0%, var(--color-frame-darkness), 0.175);
	position: absolute;
	inset: 0;
	background-color: currentColor;
	content: "";
}
</style>
