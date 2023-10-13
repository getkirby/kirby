<template>
	<div class="k-navigate">
		<slot />
	</div>
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		axis: String,
		disabled: Boolean,
		select: String
	},
	computed: {
		elements() {
			return Array.from(
				this.$el.querySelectorAll(
					this.select ?? ":where(button, a):not(:disabled)"
				)
			);
		},
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
	destroyed() {
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
			const elements = this.elements;
			let index = elements.indexOf(document.activeElement);

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

			event?.preventDefault();

			elements[next]?.focus();
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
