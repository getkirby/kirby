<template>
	<div class="k-topbar">
		<k-button
			icon="bars"
			class="k-panel-menu-proxy"
			@click="$panel.menu.toggle()"
		/>

		<k-breadcrumb :crumbs="crumbs" class="k-topbar-breadcrumb" />

		<div class="k-topbar-signals">
			<slot />
		</div>
	</div>
</template>

<script>
/**
 * @unstable
 */
export default {
	props: {
		breadcrumb: Array,
		view: Object
	},
	computed: {
		crumbs() {
			return [
				{
					link: this.view.link,
					label: this.view.label ?? this.view.breadcrumbLabel,
					icon: this.view.icon,
					loading: this.$panel.isLoading
				},
				...this.breadcrumb
			];
		}
	}
};
</script>

<style>
.k-topbar {
	position: relative;
	margin-inline: calc(var(--button-padding) * -1);
	margin-bottom: var(--spacing-8);
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
}
.k-topbar-breadcrumb {
	margin-inline-start: -2px;
	flex: 1;
}
.k-topbar-signals {
	display: flex;
	align-items: center;
}
</style>
