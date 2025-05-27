<template>
	<component :is="element" class="k-navigate">
		<slot />
	</component>
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		axis: String,
		disabled: Boolean,
		element: {
			type: String,
			default: "div"
		},
		select: {
			type: String,
			default: ":where(button, a):not(:disabled)"
		}
	},
	emits: ["next", "prev"],
	computed: {
		keys() {
			switch (this.axis) {
				case "x":
					return {
						ArrowLeft: this.prev,
						ArrowRight: this.next
					};
				case "y":
					return {
						ArrowUp: this.prev,
						ArrowDown: this.next
					};
				default:
					return {
						ArrowLeft: this.prev,
						ArrowRight: this.next,
						ArrowUp: this.prev,
						ArrowDown: this.next
					};
			}
		}
	},
	mounted() {
		this.$el.addEventListener("keydown", this.keydown);
	},
	unmounted() {
		this.$el.removeEventListener("keydown", this.keydown);
	},
	methods: {
		focus(index = 0, event) {
			this.move(index, event);
		},
		keydown(event) {
			if (this.disabled) {
				return false;
			}

			this.keys[event.key]?.apply(this, [event]);
		},
		move(next = 0, event) {
			// get all focusable elements
			const elements = [...this.$el.querySelectorAll(this.select)];

			// find the currently focused element
			let index = elements.findIndex(
				(element) =>
					element === document.activeElement ||
					element.contains(document.activeElement)
			);

			if (index === -1) {
				index = 0;
			}

			switch (next) {
				case "first":
					next = 0;
					break;
				case "next":
					next = index + 1;
					break;
				case "last":
					next = elements.length - 1;
					break;
				case "prev":
					next = index - 1;
					break;
			}

			if (next < 0) {
				this.$emit("prev");
			} else if (next >= elements.length) {
				this.$emit("next");
			} else {
				elements[next]?.focus();
			}

			event?.preventDefault();
		},
		next(event) {
			this.move("next", event);
		},
		prev(event) {
			this.move("prev", event);
		}
	}
};
</script>
