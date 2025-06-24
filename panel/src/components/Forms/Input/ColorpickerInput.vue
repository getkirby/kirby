<template>
	<fieldset
		:class="['k-colorpicker-input', $attrs.class]"
		:style="{
			'--h': hsl.h,
			'--s': hsl.s,
			'--l': hsl.l,
			'--a': hsl.a,
			...$attrs.style
		}"
	>
		<legend class="sr-only">{{ $t("color") }}</legend>
		<k-coords-input
			ref="coords"
			:autofocus="autofocus"
			:disabled="disabled"
			:required="required"
			:value="coords"
			@input="setCoords($event)"
		/>
		<label :aria-label="$t('hue')">
			<k-hue-input
				:disabled="disabled"
				:required="required"
				:value="color.h"
				@input="setHue($event)"
			/>
		</label>
		<label v-if="alpha" :aria-label="$t('alpha')">
			<k-alpha-input
				:disabled="disabled"
				:required="required"
				:value="color.a"
				@input="setAlpha($event)"
			/>
		</label>
		<k-coloroptions-input
			:disabled="disabled"
			:format="format"
			:options="options"
			:required="required"
			:value="value"
			@input="$emit('input', $event)"
		/>
		<input
			:name="name"
			:required="required"
			:value="formatted"
			class="input-hidden"
			tabindex="-1"
			type="text"
		/>
	</fieldset>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, options],
	props: {
		/**
		 * Show the alpha tange input
		 */
		alpha: {
			default: true,
			type: Boolean
		},
		/**
		 * @values "hex", "rgb", "hsl"
		 */
		format: {
			default: "hex",
			type: String,
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		value: {
			type: [Object, String]
		}
	}
};

/**
 * @since 4.0.0
 */
export default {
	mixins: [Input, props],
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
			return this.value
				? {
						x: this.color.s * 100,
						y: (1 - this.color.v) * 100
					}
				: null;
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
		focus() {
			this.$refs.coords.focus();
		},
		setAlpha(alpha) {
			this.color.a = this.alpha ? this.between(Number(alpha), 0, 1) : 1;
			this.emit();
		},
		setCoords(coords) {
			if (!coords) {
				return this.$emit("input", "");
			}

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
.k-colorpicker-input .k-coloroptions-input ul {
	grid-template-columns: repeat(6, 1fr);
}
</style>
