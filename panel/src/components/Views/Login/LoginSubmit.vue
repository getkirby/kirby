<template>
	<k-button
		:disabled="$panel.isLoading"
		:icon="$panel.isLoading ? 'loader' : icon"
		:text="label"
		size="lg"
		theme="positive"
		type="submit"
		variant="filled"
	/>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	props: {
		label: {
			type: String,
			default: () => window.panel.t("login")
		},
		icon: {
			type: String,
			default: "check"
		}
	}
};
</script>
