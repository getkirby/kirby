<template>
	<k-view-button
		:icon="current === 'light' ? 'sun' : 'moon'"
		:options="options"
		:text="$t('theme')"
	/>
</template>

<script>
/**
 * View header button to toggle the Panel theme
 * @displayName ThemeViewButton
 * @since 5.0.0
 * @unstable
 */
export default {
	computed: {
		current() {
			return this.$panel.theme.current;
		},
		options() {
			return [
				{
					text: this.$t("theme.light"),
					icon: "sun",
					disabled: this.setting === "light",
					click: () => this.$panel.theme.set("light")
				},
				{
					text: this.$t("theme.dark"),
					icon: "moon",
					disabled: this.setting === "dark",
					click: () => this.$panel.theme.set("dark")
				},
				{
					text: this.$t("theme.automatic"),
					icon: "wand",
					disabled: this.setting === null,
					click: () => this.$panel.theme.reset()
				}
			];
		},
		setting() {
			return this.$panel.theme.setting;
		}
	}
};
</script>
