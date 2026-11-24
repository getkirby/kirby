<template>
	<k-button
		:disabled="isLoading"
		:icon="isLoading ? 'loader' : icon"
		:text="label"
		size="lg"
		theme="positive"
		type="submit"
		variant="filled"
		class="k-login-submit"
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
		icon: {
			type: String,
			default: "check"
		},
		label: {
			type: String,
			default: () => window.panel.t("login")
		},
		loading: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		isLoading() {
			return this.loading === true || this.$panel.isLoading === true;
		}
	}
};
</script>
