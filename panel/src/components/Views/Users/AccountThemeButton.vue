<template>
	<k-button
		:icon="icon"
		:text="text"
		size="sm"
		variant="filled"
		@click="$dialog(dialog)"
	/>
</template>

<script>
export default {
	computed: {
		dialog() {
			return "account/theme/" + (this.isDark ? "light" : "dark");
		},
		icon() {
			return this.isDark ? "sun" : "moon";
		},
		isDark() {
			return this.$panel.user.theme === "dark";
		},
		text() {
			return this.isDark ? this.$t("dark.off") : this.$t("dark.on");
		}
	}
};
</script>
