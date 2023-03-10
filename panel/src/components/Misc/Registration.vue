<template>
	<k-bar :data-theme="theme">
		<p>{{ $t("license.unregistered") }}</p>

		<k-button-group :buttons="buttons" :responsive="true" size="sm" />
	</k-bar>
</template>

<script>
export default {
	computed: {
		buttons() {
			return [
				{
					icon: "key",
					text: this.$t("license.register"),
					title: this.$t("license.unregistered"),
					click: () => this.$dialog("registration")
				},
				{
					icon: "cart",
					text: this.$t("license.buy"),
					link: "https://getkirby.com/buy",
					target: "_blank"
				}
			];
		},
		theme() {
			return this.$system.isLocal ? "info" : "negative";
		}
	}
};
</script>

<style scoped>
.k-bar {
	position: sticky;
	bottom: 0;
	height: 3rem;
	padding-inline: var(--main-padding-inline);
	background: var(--theme-color-back);
	container-type: inline-size;
}

.k-bar p {
	font-size: var(--text-sm);
}
</style>
