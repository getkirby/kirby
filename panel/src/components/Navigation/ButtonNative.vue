<template>
	<button
		:id="id"
		:aria-current="current"
		:autofocus="autofocus"
		:data-theme="theme"
		:data-responsive="responsive"
		:role="role"
		:tabindex="tabindex"
		:title="tooltip"
		:type="type"
		class="k-button"
		@click="click"
	>
		<k-icon v-if="icon" :type="icon" :alt="tooltip" class="k-button-icon" />
		<span v-if="$slots.default" class="k-button-text"><slot /></span>
	</button>
</template>

<script>
/**
 * @deprecated 4.0.0 `<k-button-native>` has been deprecated and will be removed in v5. Use `<k-button>` instead.
 */
export default {
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		click: {
			type: Function,
			default: () => {}
		},
		current: [String, Boolean],
		icon: String,
		id: [String, Number],
		responsive: Boolean,
		role: String,
		tabindex: String,
		theme: String,
		tooltip: String,
		type: {
			type: String,
			default: "button"
		}
	},
	mounted() {
		window.panel.deprecated(
			"<k-button-native> will be removed in a future version. Use <k-button> instead."
		);
	}
};
</script>
