<template>
	<div v-if="$panel.view.id === 'account'">
		<k-button
			:dropdown="true"
			:icon="current === 'light' ? 'sun' : 'moon'"
			:text="$t('theme')"
			size="sm"
			variant="filled"
			class="k-view-theme-button"
			@click="$refs.dropdown.toggle()"
		/>
		<k-dropdown-content ref="dropdown" :options="options" align-x="end" />
	</div>
</template>

<script>
/**
 * View header button to toggle the Panel theme
 * @since 5.0.0
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
