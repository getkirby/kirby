<template>
	<div
		:aria-disabled="disabled"
		:class="['k-coords-input', $attrs.class]"
		:data-empty="!value"
		:style="$attrs.style"
		@mousedown="onDrag"
		@click="onMove"
		@keydown="onKeys"
	>
		<slot />
		<button
			:id="id"
			:autofocus="autofocus"
			:disabled="disabled"
			:style="{
				left: `${x}%`,
				top: `${y}%`
			}"
			class="k-coords-input-thumb"
			@keydown.enter.prevent="onEnter"
			@keydown.delete="onDelete"
		/>
		<input
			:name="name"
			:required="required"
			:value="value ? [value.x, value.y] : null"
			class="input-hidden"
			tabindex="-1"
			type="text"
		/>
	</div>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		reset: {
			default: true,
			type: Boolean
		},
		value: {
			default: () => {
				return {
					x: 0,
					y: 0
				};
			},
			type: Object
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-coords-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
	emits: ["input"],
	data() {
		return {
			x: 0,
			y: 0
		};
	},
	watch: {
		value: {
			handler(newValue) {
				const value = this.parse(newValue);
				this.x = value?.x ?? 0;
				this.y = value?.y ?? 0;
			},
			immediate: true
		}
	},
	methods: {
		focus() {
			this.$el.querySelector("button")?.focus();
		},
		getCoords(event, bounds) {
			return {
				x: Math.min(Math.max(event.clientX - bounds.left, 0), bounds.width),
				y: Math.min(Math.max(event.clientY - bounds.top, 0), bounds.height)
			};
		},
		onDelete() {
			if (this.reset && !this.required) {
				this.$emit("input", null);
			}
		},
		onDrag(e) {
			// only react on mousedown of main mouse button
			if (e.button !== 0) {
				return;
			}

			const moving = (e) => this.onMove(e);

			const end = () => {
				window.removeEventListener("mousemove", moving);
				window.removeEventListener("mouseup", end);
			};

			window.addEventListener("mousemove", moving);
			window.addEventListener("mouseup", end);
		},
		onEnter() {
			this.$el.form?.requestSubmit();
		},
		onInput(e, value) {
			e.preventDefault();
			e.stopPropagation();

			if (this.disabled) {
				return false;
			}

			this.x = Math.min(Math.max(parseFloat(value.x ?? this.x ?? 0), 0), 100);
			this.y = Math.min(Math.max(parseFloat(value.y ?? this.y ?? 0), 0), 100);

			this.$emit("input", {
				x: this.x,
				y: this.y
			});
		},
		onKeys(e) {
			const step = e.shiftKey ? 10 : 1;
			const keys = {
				ArrowUp: { y: this.y - step },
				ArrowDown: { y: this.y + step },
				ArrowLeft: { x: this.x - step },
				ArrowRight: { x: this.x + step }
			};

			if (keys[e.key]) {
				this.onInput(e, keys[e.key]);
			}
		},
		async onMove(e) {
			const bounds = this.$el.getBoundingClientRect();
			const coords = this.getCoords(e, bounds);
			const x = (coords.x / bounds.width) * 100;
			const y = (coords.y / bounds.height) * 100;
			this.onInput(e, { x, y });

			await this.$nextTick();
			this.focus();
		},
		parse(value) {
			if (typeof value === "object") {
				return value;
			}

			const keywords = {
				"top left": { x: 0, y: 0 },
				"top center": { x: 50, y: 0 },
				"top right": { x: 100, y: 0 },
				"center left": { x: 0, y: 50 },
				center: { x: 50, y: 50 },
				"center center": { x: 50, y: 50 },
				"center right": { x: 100, y: 50 },
				"bottom left": { x: 0, y: 100 },
				"bottom center": { x: 50, y: 100 },
				"bottom right": { x: 100, y: 100 }
			};

			if (keywords[value]) {
				return keywords[value];
			}

			const coords = value.split(",").map((coord) => coord.trim());

			return {
				x: coords[0],
				y: coords[1] ?? 0
			};
		}
	}
};
</script>

<style>
.k-coords-input {
	position: relative;
	display: block !important;
}
.k-coords-input-thumb {
	position: absolute;
	aspect-ratio: 1/1;
	width: var(--range-thumb-size);
	background: var(--range-thumb-color);
	border-radius: var(--range-thumb-size);
	box-shadow: var(--range-thumb-shadow);
	transform: translate(-50%, -50%);
	cursor: move;
}
.k-coords-input[data-empty="true"] .k-coords-input-thumb {
	opacity: 0;
}
.k-coords-input-thumb:active {
	cursor: grabbing;
}
.k-coords-input:focus-within {
	outline: var(--outline);
}
.k-coords-input[aria-disabled="true"] {
	pointer-events: none;
	opacity: var(--opacity-disabled);
}
.k-coords-input .k-coords-input-thumb:focus {
	outline: var(--outline);
}
</style>
