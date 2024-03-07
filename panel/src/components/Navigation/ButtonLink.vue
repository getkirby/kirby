<template>
	<k-link
		:id="id"
		:aria-current="current"
		:autofocus="autofocus"
		:data-theme="theme"
		:data-responsive="responsive"
		:rel="rel"
		:role="role"
		:tabindex="tabindex"
		:target="target"
		:title="tooltip"
		:to="link"
		class="k-button"
	>
		<k-icon v-if="icon" :type="icon" :alt="tooltip" class="k-button-icon" />
		<span v-if="$slots.default" class="k-button-text"><slot /></span>
	</k-link>
</template>

<script>
/**
 * @deprecated 4.0.0 `<k-button-link>` has been deprecated and will be removed in v5. Use `<k-button link="https//getkirby.com">` instead.
 */
export default {
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		current: [String, Boolean],
		icon: String,
		id: [String, Number],
		link: String,
		rel: String,
		responsive: Boolean,
		role: String,
		target: String,
		tabindex: String,
		theme: String,
		tooltip: String
	},
	mounted() {
		window.panel.deprecated(
			'<k-button-link> will be removed in a future version. Use <k-button link="https//getkirby.com"> instead.'
		);
	},
	methods: {
		focus() {
			this.$el.focus();
		}
	}
};
</script>
