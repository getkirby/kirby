<template>
	<k-bar :data-is-local="$system.isLocal">
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
		}
	}
};
</script>

<style scoped>
.k-bar {
	position: sticky;
	bottom: 0;
	height: var(--height-xl);
	padding-inline: var(--main-padding-inline);
	background: var(--color-red-500);
	container-type: inline-size;
}

.k-bar[data-is-local="true"] {
	background: var(--color-blue-300);
}

.k-bar p {
	font-size: var(--text-sm);
}
</style>
