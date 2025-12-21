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
		sizes: Object
	},
	emits: ["change"],
	computed: {
		buttons() {
			const buttons = {};

			for (const size in this.sizes) {
				buttons[size] = {
					current: this.current === size,
					icon: this.sizes[size].icon,
					size: "sm",
					theme: this.current === size ? "info" : null,
					variant: "filled"
				};
			}

			return buttons;
		}
	}
};
</script>
