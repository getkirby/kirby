<template>
	<span
		:id="id"
		:data-disabled="true"
		:data-responsive="responsive"
		:data-theme="theme"
		:title="tooltip"
		class="k-button"
	>
		<k-icon v-if="icon" :type="icon" :alt="tooltip" class="k-button-icon" />
		<span v-if="$slots.default" class="k-button-text"><slot /></span>
	</span>
</template>

<script>
/**
 * @deprecated `<k-button-disabled>` has been deprecated and will be removed in Kirby 5. Use `<k-button :disabled="true">` instead.
 * @todo Remove in v5.0
 */
export default {
	inheritAttrs: false,
	props: {
		icon: String,
		id: [String, Number],
		responsive: Boolean,
		theme: String,
		tooltip: String
	}
};
</script>

<style>
.k-button[data-disabled="true"] {
	opacity: 0.5;
	pointer-events: none;
	cursor: default;
}
.k-card-options > .k-button[data-disabled="true"] {
	display: inline-flex;
}
</style>
