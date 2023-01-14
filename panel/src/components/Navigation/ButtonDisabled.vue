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
 * @deprecated will be removed in v3.10. Use `k-button` directly
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
