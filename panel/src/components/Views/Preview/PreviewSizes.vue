<template>
	<k-button-group layout="collapsed" class="k-preview-sizes">
		<k-button
			v-for="(button, size) in buttons"
			:key="size"
			v-bind="button"
			@click="$emit('change', size)"
		/>
	</k-button-group>
</template>

<script>
/**
 * @since 6.0.0
 */
export default {
	props: {
		current: String,
		mode: String,
		sizes: Object
	},
	emits: ["change"],
	data() {
		return {
			disabled: Object.fromEntries(
				Object.keys(this.sizes).map((size) => [size, false])
			)
		};
	},
	computed: {
		buttons() {
			const buttons = {};

			for (const size in this.sizes) {
				buttons[size] = {
					current: this.current === size,
					disabled: this.disabled[size],
					icon: this.sizes[size].icon,
					size: "sm",
					theme: this.current === size ? "info-icon" : null,
					variant: "filled"
				};
			}

			return buttons;
		}
	},
	watch: {
		mode() {
			this.available();
		}
	},
	mounted() {
		this.available();
	},
	methods: {
		available() {
			const buttons = this.$el.querySelectorAll(".k-button");

			for (let index = 1; index < buttons.length; index++) {
				const sizes = Object.keys(this.sizes);

				// get corresponding size for button
				const size = parseInt(this.sizes[sizes[index - 1]].width);

				// scale the size to accommodate for the specific view mode,
				// e.g. in compare mode we need twice the size as window width
				// to be able to show two iframes at this size
				let scaled = size;

				if (this.mode === "compare") {
					scaled *= 2;
				} else if (this.mode === "form") {
					scaled += 320;
				}

				// update [data-hidden] whenever the condition changes
				const mq = matchMedia(`(min-width: ${scaled}px)`);

				this.disabled[sizes[index]] = !mq.matches;

				mq.addEventListener("change", () => {
					this.disabled[sizes[index]] = !mq.matches;
				});
			}
		}
	}
};
</script>
