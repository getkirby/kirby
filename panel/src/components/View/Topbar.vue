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
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @unstable
 */
export default {
	props: {
		breadcrumb: Array,
		view: Object
	},
	data() {
		return {
			isLoading: false
		};
	},
	computed: {
		crumbs() {
			return [
				{
					link: this.view.link,
					label: this.view.label ?? this.view.breadcrumbLabel,
					icon: this.view.icon,
					loading: this.isLoading
				},
				...this.breadcrumb
			];
		}
	},
	watch: {
		// only show the loader once loading takes a noticeable
		// moment, to avoid a flicker on fast responses
		"$panel.isLoading"(isLoading) {
			clearTimeout(this.timer);

			if (isLoading === false) {
				this.isLoading = false;
				return;
			}

			this.timer = setTimeout(() => (this.isLoading = true), 300);
		}
	},
	unmounted() {
		clearTimeout(this.timer);
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
