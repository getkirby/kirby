<template>
	<!-- Single option = button -->
	<k-button
		v-if="hasSingleOption"
		v-bind="$attrs"
		:disabled="disabled"
		:icon="options[0].icon ?? icon"
		:size="options[0].size ?? size"
		:title="options[0].title ?? options[0].tooltip ?? options[0].text"
		:variant="options[0].variant ?? variant"
		class="k-options-dropdown-toggle"
		@click="onAction(options[0].option ?? options[0].click, options[0], 0)"
	>
		<template v-if="text === true">
			{{ options[0].text }}
		</template>
		<template v-else-if="text !== false">
			{{ text }}
		</template>
	</k-button>

	<!-- Multiple options = dropdown -->
	<div v-else-if="options.length" v-bind="$attrs" class="k-options-dropdown">
		<k-button
			:disabled="disabled"
			:dropdown="true"
			:icon="icon"
			:size="size"
			:text="text !== true && text !== false ? text : null"
			:title="$t('options')"
			:variant="variant"
			class="k-options-dropdown-toggle"
			@click="$refs.options.toggle()"
		/>
		<k-dropdown-content
			ref="options"
			:align-x="align"
			:options="options"
			class="k-options-dropdown-content"
			@action="onAction"
		/>
	</div>
</template>

<script>
export default {
	props: {
		/**
		 * Aligment of the dropdown items
		 * @values "left", "right"
		 */
		align: {
			type: String,
			default: "right"
		},
		disabled: {
			type: Boolean
		},
		/**
		 * Icon for the dropdown button
		 */
		icon: {
			type: String,
			default: "dots"
		},
		options: {
			type: [Array, Function, String],
			default: () => []
		},
		/**
		 * Whether or which text to show
		 * for the dropdown button
		 */
		text: {
			type: [Boolean, String],
			default: true
		},
		/**
		 * Visual theme of the dropdown
		 * @values "dark", "light"
		 */
		theme: {
			type: String,
			default: "dark"
		},
		/**
		 * Specific size styling for the button
		 */
		size: String,
		/**
		 * Styling variant for the button
		 */
		variant: String
	},
	emits: ["action", "option"],
	computed: {
		hasSingleOption() {
			return Array.isArray(this.options) && this.options.length === 1;
		}
	},
	methods: {
		onAction(action, item, itemIndex) {
			if (typeof action === "function") {
				action.call(this);
			} else {
				this.$emit("action", action, item, itemIndex);
				this.$emit("option", action, item, itemIndex);
			}
		},
		toggle(opener = this.$el) {
			this.$refs.options.toggle(opener);
		}
	}
};
</script>

<style>
.k-options-dropdown {
	display: flex;
	justify-content: center;
	align-items: center;
}
</style>
