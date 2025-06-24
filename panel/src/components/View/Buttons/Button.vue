<template>
	<div class="k-view-button">
		<k-button
			v-bind="$props"
			:dropdown="dropdown || hasDropdown"
			@click="onClick"
		/>
		<k-dropdown-content
			v-if="hasDropdown"
			ref="dropdown"
			:options="Array.isArray(options) ? options : $dropdown(options)"
			align-x="end"
			@action="$emit('action', $event)"
		/>
	</div>
</template>

<script>
import Button from "@/components/Navigation/Button.vue";

/**
 * @displayName ViewButton
 * @since 5.0.0
 */
export default {
	extends: Button,
	props: {
		options: [Array, String],
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "sm"
		},
		// eslint-disable-next-line vue/require-prop-types
		variant: {
			default: "filled"
		}
	},
	emits: ["action", "click"],
	computed: {
		hasDropdown() {
			if (Array.isArray(this.options) === true) {
				return this.options.length > 0;
			}

			return Boolean(this.options);
		}
	},
	methods: {
		onClick() {
			if (this.hasDropdown) {
				return this.$refs.dropdown.toggle();
			}

			this.$emit("click");
		}
	}
};
</script>
