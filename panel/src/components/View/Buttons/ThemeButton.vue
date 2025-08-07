<template>
	<k-view-button
		v-bind="$props"
		:icon="current === 'light' ? 'sun' : 'moon'"
		:options="options"
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
	props: {
		/**
		 * @since 5.0.1
		 */
		size: String,
		/**
		 * @since 5.0.1
		 */
		text: {
			type: String,
			default: () => window.panel.t("theme")
		},
		/**
		 * @since 5.0.1
		 */
		variant: String
	},
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
					disabled: this.setting === "system",
					click: () => this.$panel.theme.set("system")
				}
			];
		},
		setting() {
			return this.$panel.theme.setting ?? this.$panel.theme.config;
		}
	}
};
</script>
