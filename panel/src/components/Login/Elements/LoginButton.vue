<template>
	<k-button
		:icon="loading ? 'loader' : 'check'"
		:text="text"
		class="k-login-button"
		size="lg"
		theme="positive"
		type="submit"
		variant="filled"
	/>
</template>

<script>
export default {
	props: {
		loading: Boolean,
		text: {
			type: String,
			default() {
				return window.panel.t("login");
			}
		}
	}
};
</script>
