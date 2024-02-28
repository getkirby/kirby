<template>
	<k-button
		:icon="icon"
		:text="text"
		size="sm"
		variant="filled"
		@click="$panel.theme.toggle()"
	/>
</template>

<script>
export default {
	computed: {
		icon() {
			return this.isDark ? "sun" : "moon";
		},
		isDark() {
			return this.$panel.theme.current === "dark";
		},
		text() {
			return this.isDark ? this.$t("dark.off") : this.$t("dark.on");
		}
	}
};
</script>
