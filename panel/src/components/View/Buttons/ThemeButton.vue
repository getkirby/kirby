<template>
	<k-view-button
		:dropdown="options"
		:icon="icon"
		:text="$t('theme')"
		class="k-view-theme-button"
		@click.native="$panel.theme.toggle()"
	/>
</template>

<script>
/**
 * View header button to toggle the Panel theme
 * @displayName ViewThemeButton
 * @since 5.0.0
 */
export default {
	computed: {
		current() {
			return this.$panel.theme.current;
		},
		icon() {
			return this.current === "light" ? "sun" : "moon";
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
